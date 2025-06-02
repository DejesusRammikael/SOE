<?php
require_once 'db_connection.php';

// Add category column if it doesn't exist
$alter_table = "ALTER TABLE menu_items ADD COLUMN IF NOT EXISTS category varchar(50) DEFAULT 'Food'";
if ($conn->query($alter_table)) {
    echo "Successfully added category column\n";
} else {
    echo "Error adding category column: " . $conn->error . "\n";
}

// Update beverages
$update_beverages = "UPDATE menu_items SET category = 'Beverage' WHERE name LIKE '%coffee%' OR name LIKE '%tea%' OR name LIKE '%juice%' OR name LIKE '%drink%' OR name LIKE '%espresso%' OR name LIKE '%cappuccino%' OR name LIKE '%brew%'";
if ($conn->query($update_beverages)) {
    echo "Successfully updated beverage categories\n";
} else {
    echo "Error updating beverage categories: " . $conn->error . "\n";
}

// Update desserts
$update_desserts = "UPDATE menu_items SET category = 'Dessert' WHERE name LIKE '%cake%' OR name LIKE '%ice cream%' OR name LIKE '%dessert%' OR name LIKE '%sweet%' OR name LIKE '%cookie%'";
if ($conn->query($update_desserts)) {
    echo "Successfully updated dessert categories\n";
} else {
    echo "Error updating dessert categories: " . $conn->error . "\n";
}

// Update remaining items to Food
$update_food = "UPDATE menu_items SET category = 'Food' WHERE category IS NULL";
if ($conn->query($update_food)) {
    echo "Successfully updated food categories\n";
} else {
    echo "Error updating food categories: " . $conn->error . "\n";
}

echo "Database update complete!\n";
?> 