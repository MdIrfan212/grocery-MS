-- database/seed.sql
-- Default admin user: admin@example.com / admin123 (please change immediately)
INSERT INTO users (name, email, password_hash, role, status) VALUES
('Admin', 'admin@example.com', '$2y$10$4wN/3x9rQb2Q3bJz7m7c5eJ8z8z7V2jJw2y1YgkV4/1lS9m3zPrLy', 'admin','active');

-- Some categories
INSERT INTO categories (name) VALUES ('Vegetables'), ('Fruits'), ('Dairy') 
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Sample products
INSERT INTO products (name, sku, price, stock, category_id) VALUES
('Tomato', 'SKU-TOM', 60.00, 50, (SELECT id FROM categories WHERE name='Vegetables' LIMIT 1)),
('Banana', 'SKU-BAN', 80.00, 100, (SELECT id FROM categories WHERE name='Fruits' LIMIT 1)),
('Milk 1L', 'SKU-MLK', 120.00, 30, (SELECT id FROM categories WHERE name='Dairy' LIMIT 1));
