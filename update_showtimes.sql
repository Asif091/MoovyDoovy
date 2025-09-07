-- Update showtimes to include current and next few days
-- This will ensure movies are visible for booking

USE CinemaDB;

-- Delete old showtimes that might be in the past
DELETE FROM showtime WHERE date < CURDATE();

-- Get admin and hall IDs
SET @admin_user_id = (SELECT user_id FROM users WHERE email = 'admin@moovydoovy.com' LIMIT 1);
SET @hall_a_id = (SELECT hall_id FROM hall WHERE hall_name = 'Hall A' LIMIT 1);
SET @hall_b_id = (SELECT hall_id FROM hall WHERE hall_name = 'Hall B' LIMIT 1);
SET @hall_c_id = (SELECT hall_id FROM hall WHERE hall_name = 'Hall C' LIMIT 1);

-- Insert showtimes for today, tomorrow, and day after tomorrow
INSERT INTO showtime (date, start, end, movie_name, hall_id, user_id) VALUES 
-- Today's shows
(CURDATE(), '14:00:00', '17:01:00', 'Avengers: Endgame', @hall_a_id, @admin_user_id),
(CURDATE(), '18:00:00', '20:32:00', 'The Dark Knight', @hall_b_id, @admin_user_id),
(CURDATE(), '20:00:00', '22:28:00', 'Inception', @hall_c_id, @admin_user_id),

-- Tomorrow's shows (next day)
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '12:00:00', '15:01:00', 'Avengers: Endgame', @hall_a_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '16:00:00', '18:32:00', 'The Dark Knight', @hall_a_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', '16:28:00', 'Inception', @hall_b_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '18:00:00', '21:14:00', 'Titanic', @hall_b_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '15:00:00', '16:58:00', 'The Lion King', @hall_c_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '19:00:00', '20:58:00', 'The Lion King', @hall_c_id, @admin_user_id),

-- Day after tomorrow (2nd day)
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '13:00:00', '16:01:00', 'Avengers: Endgame', @hall_a_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '17:00:00', '19:32:00', 'The Dark Knight', @hall_a_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '21:00:00', '23:28:00', 'Inception', @hall_a_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '14:00:00', '17:14:00', 'Titanic', @hall_b_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '18:00:00', '19:58:00', 'The Lion King', @hall_b_id, @admin_user_id),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '16:00:00', '17:58:00', 'The Lion King', @hall_c_id, @admin_user_id)

ON DUPLICATE KEY UPDATE 
date = VALUES(date),
start = VALUES(start),
end = VALUES(end),
movie_name = VALUES(movie_name),
hall_id = VALUES(hall_id),
user_id = VALUES(user_id);

-- Display current showtimes
SELECT 'Updated showtimes for current booking period:' AS message;
SELECT 
    s.date,
    s.start,
    s.end,
    s.movie_name,
    h.hall_name
FROM showtime s 
JOIN hall h ON s.hall_id = h.hall_id 
WHERE s.date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 2 DAY)
ORDER BY s.date, s.start;