-- Add new admin users to the MoovyDoovy system
USE CinemaDB;

-- Insert new admin users
INSERT IGNORE INTO users (first_name, last_name, email, phone, password) VALUES 
('Admin', 'One', 'admin1@email.com', '01700000010', 'admin12'),
('Admin', 'Two', 'admin2@email.com', '01700000020', 'admin12');

-- Get admin user IDs
SET @admin1_user_id = (SELECT user_id FROM users WHERE email = 'admin1@email.com');
SET @admin2_user_id = (SELECT user_id FROM users WHERE email = 'admin2@email.com');

-- Insert admin records
INSERT IGNORE INTO admin (user_id, joining_date, salary, address) VALUES 
(@admin1_user_id, '2023-01-01', 2000000.00, '456 Admin Street, Dhaka'),
(@admin2_user_id, '2023-01-01', 2000000.00, '789 Manager Avenue, Dhaka');

-- Display success message
SELECT 'New admin users added successfully!' AS message;
SELECT 'Login credentials:' AS info;
SELECT 'admin1@email.com / admin12' AS admin1_login;
SELECT 'admin2@email.com / admin12' AS admin2_login;