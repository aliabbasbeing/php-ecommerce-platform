<?php

namespace App\Models;

class Category extends BaseModel
{
    protected $table = 'categories';
    protected $fillable = [
        'name', 'slug', 'description', 'image', 'parent_id', 'sort_order',
        'is_active', 'meta_title', 'meta_description'
    ];

    /**
     * Find category by slug
     */
    public function findBySlug($slug)
    {
        return $this->findWhere(['slug' => $slug, 'is_active' => 1]);
    }

    /**
     * Get all active categories
     */
    public function getActive()
    {
        return $this->findAll(['is_active' => 1], 'sort_order ASC, name ASC');
    }

    /**
     * Get parent categories (top-level)
     */
    public function getParentCategories()
    {
        return $this->findAll(['parent_id' => null, 'is_active' => 1], 'sort_order ASC, name ASC');
    }

    /**
     * Get child categories
     */
    public function getChildCategories($parentId)
    {
        return $this->findAll(['parent_id' => $parentId, 'is_active' => 1], 'sort_order ASC, name ASC');
    }

    /**
     * Get category tree
     */
    public function getCategoryTree()
    {
        $categories = $this->getActive();
        return $this->buildTree($categories);
    }

    /**
     * Build category tree structure
     */
    protected function buildTree($categories, $parentId = null)
    {
        $tree = [];
        
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $category['children'] = $this->buildTree($categories, $category['id']);
                $tree[] = $category;
            }
        }
        
        return $tree;
    }

    /**
     * Get category breadcrumbs
     */
    public function getBreadcrumbs($categoryId)
    {
        $breadcrumbs = [];
        $category = $this->find($categoryId);
        
        while ($category) {
            array_unshift($breadcrumbs, $category);
            $category = $category['parent_id'] ? $this->find($category['parent_id']) : null;
        }
        
        return $breadcrumbs;
    }

    /**
     * Get products count for category
     */
    public function getProductCount($categoryId, $includeChildren = true)
    {
        if ($includeChildren) {
            $categoryIds = $this->getAllChildIds($categoryId);
            $categoryIds[] = $categoryId;
            
            $placeholders = str_repeat('?,', count($categoryIds) - 1) . '?';
            $sql = "SELECT COUNT(DISTINCT pc.product_id) 
                    FROM product_categories pc
                    JOIN products p ON pc.product_id = p.id
                    WHERE pc.category_id IN ({$placeholders}) AND p.is_active = 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($categoryIds);
            return $stmt->fetchColumn();
        } else {
            $sql = "SELECT COUNT(pc.product_id) 
                    FROM product_categories pc
                    JOIN products p ON pc.product_id = p.id
                    WHERE pc.category_id = ? AND p.is_active = 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$categoryId]);
            return $stmt->fetchColumn();
        }
    }

    /**
     * Get all child category IDs
     */
    public function getAllChildIds($parentId)
    {
        $childIds = [];
        $children = $this->getChildCategories($parentId);
        
        foreach ($children as $child) {
            $childIds[] = $child['id'];
            $childIds = array_merge($childIds, $this->getAllChildIds($child['id']));
        }
        
        return $childIds;
    }

    /**
     * Check if category has children
     */
    public function hasChildren($categoryId)
    {
        $children = $this->getChildCategories($categoryId);
        return !empty($children);
    }

    /**
     * Get featured categories
     */
    public function getFeatured($limit = 6)
    {
        $sql = "SELECT c.*, COUNT(pc.product_id) as product_count
                FROM categories c
                LEFT JOIN product_categories pc ON c.id = pc.category_id
                LEFT JOIN products p ON pc.product_id = p.id AND p.is_active = 1
                WHERE c.is_active = 1 AND c.parent_id IS NULL
                GROUP BY c.id
                HAVING product_count > 0
                ORDER BY c.sort_order ASC, product_count DESC
                LIMIT ?";
        
        return $this->query($sql, [$limit]);
    }

    /**
     * Search categories
     */
    public function search($query)
    {
        $sql = "SELECT * FROM categories 
                WHERE is_active = 1 AND (name LIKE ? OR description LIKE ?)
                ORDER BY name ASC";
        
        $searchTerm = '%' . $query . '%';
        return $this->query($sql, [$searchTerm, $searchTerm]);
    }
}

class Coupon extends BaseModel
{
    protected $table = 'coupons';
    protected $fillable = [
        'code', 'type', 'value', 'minimum_amount', 'maximum_discount',
        'usage_limit', 'used_count', 'is_active', 'starts_at', 'expires_at'
    ];

    /**
     * Find coupon by code
     */
    public function findByCode($code)
    {
        return $this->findWhere(['code' => strtoupper($code)]);
    }

    /**
     * Validate coupon
     */
    public function validateCoupon($code, $orderAmount = 0)
    {
        $coupon = $this->findByCode($code);
        
        if (!$coupon) {
            return ['valid' => false, 'message' => 'Coupon not found.'];
        }

        if (!$coupon['is_active']) {
            return ['valid' => false, 'message' => 'Coupon is inactive.'];
        }

        $now = date('Y-m-d H:i:s');
        
        if ($coupon['starts_at'] && $coupon['starts_at'] > $now) {
            return ['valid' => false, 'message' => 'Coupon is not yet active.'];
        }

        if ($coupon['expires_at'] && $coupon['expires_at'] < $now) {
            return ['valid' => false, 'message' => 'Coupon has expired.'];
        }

        if ($coupon['usage_limit'] && $coupon['used_count'] >= $coupon['usage_limit']) {
            return ['valid' => false, 'message' => 'Coupon usage limit reached.'];
        }

        if ($orderAmount < $coupon['minimum_amount']) {
            return [
                'valid' => false, 
                'message' => "Minimum order amount of $" . number_format($coupon['minimum_amount'], 2) . " required."
            ];
        }

        return ['valid' => true, 'coupon' => $coupon];
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscount($code, $orderAmount)
    {
        $validation = $this->validateCoupon($code, $orderAmount);
        
        if (!$validation['valid']) {
            return 0;
        }

        $coupon = $validation['coupon'];
        
        if ($coupon['type'] === 'percentage') {
            $discount = $orderAmount * ($coupon['value'] / 100);
        } else {
            $discount = $coupon['value'];
        }

        // Apply maximum discount limit if set
        if ($coupon['maximum_discount'] && $discount > $coupon['maximum_discount']) {
            $discount = $coupon['maximum_discount'];
        }

        return min($discount, $orderAmount);
    }

    /**
     * Apply coupon (increment usage count)
     */
    public function applyCoupon($code)
    {
        $coupon = $this->findByCode($code);
        
        if ($coupon) {
            return $this->update($coupon['id'], [
                'used_count' => $coupon['used_count'] + 1
            ]);
        }
        
        return false;
    }

    /**
     * Get active coupons
     */
    public function getActive()
    {
        $now = date('Y-m-d H:i:s');
        
        $sql = "SELECT * FROM coupons 
                WHERE is_active = 1 
                AND (starts_at IS NULL OR starts_at <= ?) 
                AND (expires_at IS NULL OR expires_at >= ?)
                AND (usage_limit IS NULL OR used_count < usage_limit)
                ORDER BY created_at DESC";
        
        return $this->query($sql, [$now, $now]);
    }
}