<?php

namespace App\Models;

class Product extends BaseModel
{
    protected $table = 'products';
    protected $fillable = [
        'name', 'slug', 'description', 'short_description', 'sku', 'price', 
        'sale_price', 'cost_price', 'stock_quantity', 'manage_stock', 'stock_status',
        'weight', 'length', 'width', 'height', 'image', 'gallery', 
        'is_active', 'is_featured', 'meta_title', 'meta_description'
    ];

    /**
     * Find product by slug
     */
    public function findBySlug($slug)
    {
        return $this->findWhere(['slug' => $slug, 'is_active' => 1]);
    }

    /**
     * Find product by SKU
     */
    public function findBySku($sku)
    {
        return $this->findWhere(['sku' => $sku]);
    }

    /**
     * Get featured products
     */
    public function getFeatured($limit = 8)
    {
        return $this->findAll(['is_featured' => 1, 'is_active' => 1], 'created_at DESC', $limit);
    }

    /**
     * Get products by category
     */
    public function getByCategory($categoryId, $limit = 20, $offset = 0)
    {
        $sql = "SELECT p.* FROM products p
                JOIN product_categories pc ON p.id = pc.product_id
                WHERE pc.category_id = ? AND p.is_active = 1
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";
        return $this->query($sql, [$categoryId, $limit, $offset]);
    }

    /**
     * Search products
     */
    public function search($query, $filters = [], $limit = 20, $offset = 0)
    {
        $sql = "SELECT p.*, AVG(r.rating) as average_rating, COUNT(r.id) as review_count
                FROM products p
                LEFT JOIN reviews r ON p.id = r.product_id AND r.is_approved = 1
                WHERE p.is_active = 1";
        $params = [];

        // Full-text search
        if (!empty($query)) {
            $sql .= " AND MATCH(p.name, p.description, p.short_description) AGAINST(? IN NATURAL LANGUAGE MODE)";
            $params[] = $query;
        }

        // Category filter
        if (!empty($filters['category_id'])) {
            $sql .= " AND p.id IN (SELECT product_id FROM product_categories WHERE category_id = ?)";
            $params[] = $filters['category_id'];
        }

        // Price range filter
        if (!empty($filters['min_price'])) {
            $sql .= " AND COALESCE(p.sale_price, p.price) >= ?";
            $params[] = $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $sql .= " AND COALESCE(p.sale_price, p.price) <= ?";
            $params[] = $filters['max_price'];
        }

        // Stock status filter
        if (!empty($filters['in_stock_only'])) {
            $sql .= " AND p.stock_status = 'in_stock'";
        }

        $sql .= " GROUP BY p.id";

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'relevance';
        switch ($sortBy) {
            case 'price_low':
                $sql .= " ORDER BY COALESCE(p.sale_price, p.price) ASC";
                break;
            case 'price_high':
                $sql .= " ORDER BY COALESCE(p.sale_price, p.price) DESC";
                break;
            case 'newest':
                $sql .= " ORDER BY p.created_at DESC";
                break;
            case 'rating':
                $sql .= " ORDER BY average_rating DESC";
                break;
            default:
                $sql .= " ORDER BY p.created_at DESC";
        }

        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->query($sql, $params);
    }

    /**
     * Get related products
     */
    public function getRelated($productId, $limit = 4)
    {
        $sql = "SELECT DISTINCT p2.* FROM products p1
                JOIN product_categories pc1 ON p1.id = pc1.product_id
                JOIN product_categories pc2 ON pc1.category_id = pc2.category_id
                JOIN products p2 ON pc2.product_id = p2.id
                WHERE p1.id = ? AND p2.id != ? AND p2.is_active = 1
                ORDER BY RAND()
                LIMIT ?";
        return $this->query($sql, [$productId, $productId, $limit]);
    }

    /**
     * Get product categories
     */
    public function getCategories($productId)
    {
        $sql = "SELECT c.* FROM categories c
                JOIN product_categories pc ON c.id = pc.category_id
                WHERE pc.product_id = ? AND c.is_active = 1
                ORDER BY c.name";
        return $this->query($sql, [$productId]);
    }

    /**
     * Get product attributes
     */
    public function getAttributes($productId)
    {
        $sql = "SELECT * FROM product_attributes WHERE product_id = ? ORDER BY name";
        return $this->query($sql, [$productId]);
    }

    /**
     * Get product reviews
     */
    public function getReviews($productId, $limit = 10, $offset = 0)
    {
        $sql = "SELECT r.*, u.first_name, u.last_name
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                WHERE r.product_id = ? AND r.is_approved = 1
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?";
        return $this->query($sql, [$productId, $limit, $offset]);
    }

    /**
     * Get product review summary
     */
    public function getReviewSummary($productId)
    {
        $sql = "SELECT 
                    COUNT(*) as total_reviews,
                    AVG(rating) as average_rating,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                FROM reviews 
                WHERE product_id = ? AND is_approved = 1";
        $result = $this->query($sql, [$productId]);
        return $result[0] ?? null;
    }

    /**
     * Update stock quantity
     */
    public function updateStock($productId, $quantity, $operation = 'decrease')
    {
        $product = $this->find($productId);
        if (!$product || !$product['manage_stock']) {
            return true;
        }

        $newQuantity = $operation === 'decrease' 
            ? $product['stock_quantity'] - $quantity
            : $product['stock_quantity'] + $quantity;

        $stockStatus = $newQuantity <= 0 ? 'out_of_stock' : 'in_stock';

        return $this->update($productId, [
            'stock_quantity' => max(0, $newQuantity),
            'stock_status' => $stockStatus
        ]);
    }

    /**
     * Check if product is in stock
     */
    public function isInStock($productId, $quantity = 1)
    {
        $product = $this->find($productId);
        if (!$product || !$product['is_active']) {
            return false;
        }

        if (!$product['manage_stock']) {
            return $product['stock_status'] === 'in_stock';
        }

        return $product['stock_quantity'] >= $quantity;
    }

    /**
     * Get low stock products
     */
    public function getLowStock($threshold = 10)
    {
        $sql = "SELECT * FROM products 
                WHERE manage_stock = 1 AND stock_quantity <= ? AND is_active = 1
                ORDER BY stock_quantity ASC";
        return $this->query($sql, [$threshold]);
    }

    /**
     * Get recent products
     */
    public function getRecent($limit = 10)
    {
        return $this->findAll(['is_active' => 1], 'created_at DESC', $limit);
    }

    /**
     * Get product gallery
     */
    public function getGallery($productId)
    {
        $product = $this->find($productId);
        if ($product && $product['gallery']) {
            return json_decode($product['gallery'], true) ?: [];
        }
        return [];
    }

    /**
     * Update product gallery
     */
    public function updateGallery($productId, $images)
    {
        $gallery = json_encode($images);
        return $this->update($productId, ['gallery' => $gallery]);
    }
}