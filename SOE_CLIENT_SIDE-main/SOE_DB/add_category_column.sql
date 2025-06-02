-- Add category column if it doesn't exist
ALTER TABLE menu_items ADD COLUMN IF NOT EXISTS category varchar(50) DEFAULT 'Meals';

-- Update existing items with appropriate categories
UPDATE menu_items SET category = 'Beverage' WHERE name LIKE '%coffee%' OR name LIKE '%tea%' OR name LIKE '%juice%' OR name LIKE '%drink%' OR name LIKE '%espresso%' OR name LIKE '%cappuccino%' OR name LIKE '%brew%';
UPDATE menu_items SET category = 'Desserts' WHERE name LIKE '%cake%' OR name LIKE '%ice cream%' OR name LIKE '%dessert%' OR name LIKE '%sweet%' OR name LIKE '%cookie%';
UPDATE menu_items SET category = 'Meals' WHERE category IS NULL; 