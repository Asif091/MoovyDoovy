-- Sample data for testing MoovyDoovy cinema booking system
-- Run this after importing MoovyDoovy.sql
-- This script can be run multiple times safely

USE CinemaDB;

-- Check and insert sample admin users only if not exists
INSERT IGNORE INTO users (first_name, last_name, email, phone, password) VALUES 
('Admin', 'User', 'admin@moovydoovy.com', '01700000000', 'admin123'),
('Admin', 'One', 'admin1@email.com', '01700000010', 'admin12'),
('Admin', 'Two', 'admin2@email.com', '01700000020', 'admin12');

-- Get admin user IDs (whether just inserted or already exists)
SET @admin_user_id = (SELECT user_id FROM users WHERE email = 'admin@moovydoovy.com' ORDER BY user_id ASC LIMIT 1);
SET @admin1_user_id = (SELECT user_id FROM users WHERE email = 'admin1@email.com' ORDER BY user_id ASC LIMIT 1);
SET @admin2_user_id = (SELECT user_id FROM users WHERE email = 'admin2@email.com' ORDER BY user_id ASC LIMIT 1);

-- Insert admin records only if not exists
INSERT IGNORE INTO admin (user_id, joining_date, salary, address) VALUES 
(@admin_user_id, '2023-01-01', 2500000.00, '123 Cinema Street, Dhaka'),
(@admin1_user_id, '2023-01-01', 2000000.00, '456 Admin Street, Dhaka'),
(@admin2_user_id, '2023-01-01', 2000000.00, '789 Manager Avenue, Dhaka');

-- Insert sample customer users only if not exists
INSERT IGNORE INTO users (first_name, last_name, email, phone, password) VALUES 
('John', 'Doe', 'john@example.com', '01700000001', 'password123'),
('Jane', 'Smith', 'jane@example.com', '01700000002', 'password123'),
('Mike', 'Johnson', 'mike@example.com', '01700000003', 'password123');

-- Get customer user IDs
SET @john_user_id = (SELECT user_id FROM users WHERE email = 'john@example.com' ORDER BY user_id ASC LIMIT 1);
SET @jane_user_id = (SELECT user_id FROM users WHERE email = 'jane@example.com' ORDER BY user_id ASC LIMIT 1);
SET @mike_user_id = (SELECT user_id FROM users WHERE email = 'mike@example.com' ORDER BY user_id ASC LIMIT 1);

-- Insert customer records only if not exists
INSERT IGNORE INTO customer (user_id, customer_type) VALUES 
(@john_user_id, 'general'),
(@jane_user_id, 'premium'),
(@mike_user_id, 'general');

-- Insert sample halls only if not exists
INSERT IGNORE INTO hall (hall_name, user_id) VALUES 
('Hall A', @admin_user_id),
('Hall B', @admin_user_id),
('Hall C', @admin_user_id);

-- Get hall IDs (whether just inserted or already exists)
SET @hall_a_id = (SELECT hall_id FROM hall WHERE hall_name = 'Hall A' ORDER BY hall_id ASC LIMIT 1);
SET @hall_b_id = (SELECT hall_id FROM hall WHERE hall_name = 'Hall B' ORDER BY hall_id ASC LIMIT 1);
SET @hall_c_id = (SELECT hall_id FROM hall WHERE hall_name = 'Hall C' ORDER BY hall_id ASC LIMIT 1);

-- Insert hall seats (80 seats per hall - 8 rows x 10 seats)
INSERT IGNORE INTO hall_seat_no (hall_id, hall_seat_no) 
SELECT @hall_a_id, seat_num FROM (
    SELECT 1 as seat_num UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION 
    SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION
    SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION 
    SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION SELECT 20 UNION
    SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION 
    SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29 UNION SELECT 30 UNION
    SELECT 31 UNION SELECT 32 UNION SELECT 33 UNION SELECT 34 UNION SELECT 35 UNION 
    SELECT 36 UNION SELECT 37 UNION SELECT 38 UNION SELECT 39 UNION SELECT 40 UNION
    SELECT 41 UNION SELECT 42 UNION SELECT 43 UNION SELECT 44 UNION SELECT 45 UNION 
    SELECT 46 UNION SELECT 47 UNION SELECT 48 UNION SELECT 49 UNION SELECT 50 UNION
    SELECT 51 UNION SELECT 52 UNION SELECT 53 UNION SELECT 54 UNION SELECT 55 UNION 
    SELECT 56 UNION SELECT 57 UNION SELECT 58 UNION SELECT 59 UNION SELECT 60 UNION
    SELECT 61 UNION SELECT 62 UNION SELECT 63 UNION SELECT 64 UNION SELECT 65 UNION 
    SELECT 66 UNION SELECT 67 UNION SELECT 68 UNION SELECT 69 UNION SELECT 70 UNION
    SELECT 71 UNION SELECT 72 UNION SELECT 73 UNION SELECT 74 UNION SELECT 75 UNION 
    SELECT 76 UNION SELECT 77 UNION SELECT 78 UNION SELECT 79 UNION SELECT 80
) seats;

INSERT IGNORE INTO hall_seat_no (hall_id, hall_seat_no) 
SELECT @hall_b_id, seat_num FROM (
    SELECT 1 as seat_num UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION 
    SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION
    SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION 
    SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION SELECT 20 UNION
    SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION 
    SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29 UNION SELECT 30 UNION
    SELECT 31 UNION SELECT 32 UNION SELECT 33 UNION SELECT 34 UNION SELECT 35 UNION 
    SELECT 36 UNION SELECT 37 UNION SELECT 38 UNION SELECT 39 UNION SELECT 40 UNION
    SELECT 41 UNION SELECT 42 UNION SELECT 43 UNION SELECT 44 UNION SELECT 45 UNION 
    SELECT 46 UNION SELECT 47 UNION SELECT 48 UNION SELECT 49 UNION SELECT 50 UNION
    SELECT 51 UNION SELECT 52 UNION SELECT 53 UNION SELECT 54 UNION SELECT 55 UNION 
    SELECT 56 UNION SELECT 57 UNION SELECT 58 UNION SELECT 59 UNION SELECT 60 UNION
    SELECT 61 UNION SELECT 62 UNION SELECT 63 UNION SELECT 64 UNION SELECT 65 UNION 
    SELECT 66 UNION SELECT 67 UNION SELECT 68 UNION SELECT 69 UNION SELECT 70 UNION
    SELECT 71 UNION SELECT 72 UNION SELECT 73 UNION SELECT 74 UNION SELECT 75 UNION 
    SELECT 76 UNION SELECT 77 UNION SELECT 78 UNION SELECT 79 UNION SELECT 80
) seats;

INSERT IGNORE INTO hall_seat_no (hall_id, hall_seat_no) 
SELECT @hall_c_id, seat_num FROM (
    SELECT 1 as seat_num UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION 
    SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION
    SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION 
    SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION SELECT 20 UNION
    SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION 
    SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29 UNION SELECT 30 UNION
    SELECT 31 UNION SELECT 32 UNION SELECT 33 UNION SELECT 34 UNION SELECT 35 UNION 
    SELECT 36 UNION SELECT 37 UNION SELECT 38 UNION SELECT 39 UNION SELECT 40 UNION
    SELECT 41 UNION SELECT 42 UNION SELECT 43 UNION SELECT 44 UNION SELECT 45 UNION 
    SELECT 46 UNION SELECT 47 UNION SELECT 48 UNION SELECT 49 UNION SELECT 50 UNION
    SELECT 51 UNION SELECT 52 UNION SELECT 53 UNION SELECT 54 UNION SELECT 55 UNION 
    SELECT 56 UNION SELECT 57 UNION SELECT 58 UNION SELECT 59 UNION SELECT 60 UNION
    SELECT 61 UNION SELECT 62 UNION SELECT 63 UNION SELECT 64 UNION SELECT 65 UNION 
    SELECT 66 UNION SELECT 67 UNION SELECT 68 UNION SELECT 69 UNION SELECT 70 UNION
    SELECT 71 UNION SELECT 72 UNION SELECT 73 UNION SELECT 74 UNION SELECT 75 UNION 
    SELECT 76 UNION SELECT 77 UNION SELECT 78 UNION SELECT 79 UNION SELECT 80
) seats;

-- Insert sample movies only if not exists
INSERT IGNORE INTO movie (movie_name, genre, duration, release_date, details, user_id) VALUES 
('Avengers: Endgame', 'Action', 181, '2023-05-01', 'The epic conclusion to the Infinity Saga that became a defining moment in cinematic history.', @admin1_user_id),
('The Dark Knight', 'Action', 152, '2023-06-15', 'Batman faces the Joker in this critically acclaimed superhero thriller.', @admin1_user_id),
('Inception', 'Sci-Fi', 148, '2023-07-20', 'A mind-bending thriller about dreams within dreams.', @admin2_user_id),
('Titanic', 'Romance', 194, '2023-08-10', 'The epic love story aboard the ill-fated ship.', @admin2_user_id),
('The Lion King', 'Animation', 118, '2023-09-05', 'The beloved Disney classic about a young lion prince.', @admin1_user_id);

-- Insert sample showtimes (next 7 days)
INSERT IGNORE INTO showtime (date, start, end, movie_name, hall_id, user_id) VALUES 
(CURDATE(), '14:00:00', '17:01:00', 'Avengers: Endgame', @hall_a_id, @admin_user_id),
(CURDATE(), '18:00:00', '20:32:00', 'The Dark Knight', @hall_b_id, @admin_user_id),
(CURDATE(), '20:00:00', '22:28:00', 'Inception', @hall_c_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '12:00:00', '15:01:00', 'Avengers: Endgame', @hall_a_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '16:00:00', '18:32:00', 'The Dark Knight', @hall_a_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', '16:28:00', 'Inception', @hall_b_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '18:00:00', '21:14:00', 'Titanic', @hall_b_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '15:00:00', '16:58:00', 'The Lion King', @hall_c_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '19:00:00', '20:58:00', 'The Lion King', @hall_c_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '13:00:00', '16:01:00', 'Avengers: Endgame', @hall_a_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '17:00:00', '19:32:00', 'The Dark Knight', @hall_a_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '21:00:00', '23:28:00', 'Inception', @hall_a_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '14:00:00', '17:14:00', 'Titanic', @hall_b_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '18:00:00', '19:58:00', 'The Lion King', @hall_b_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '16:00:00', '17:58:00', 'The Lion King', @hall_c_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '15:00:00', '18:01:00', 'Avengers: Endgame', @hall_a_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '19:00:00', '21:32:00', 'The Dark Knight', @hall_a_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '13:00:00', '15:28:00', 'Inception', @hall_b_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '16:00:00', '19:14:00', 'Titanic', @hall_b_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '20:00:00', '21:58:00', 'The Lion King', @hall_b_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '14:00:00', '15:58:00', 'The Lion King', @hall_c_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '17:00:00', '20:14:00', 'Titanic', @hall_c_id, @admin_user_id);

-- Add some sample bookings to test occupied seats
INSERT IGNORE INTO booking (ticket_price, show_id, user_id) VALUES 
(1000.00, 1, @john_user_id),
(500.00, 2, @jane_user_id);

INSERT IGNORE INTO booking_seat_no (booking_id, booking_seat_no) VALUES 
(1, 15),
(1, 16),
(2, 25);

INSERT IGNORE INTO payment (booking_id, payment_status) VALUES 
(1, 'completed'),
(2, 'completed');

-- Display success message
SELECT 'Sample data inserted successfully!' AS message;
SELECT 'You can now login with:' AS info;
SELECT 'Customer: john@example.com / password123' AS customer_login;
SELECT 'Customer: jane@example.com / password123' AS customer_login2;
SELECT 'Admin: admin@moovydoovy.com / admin123' AS admin_login;
