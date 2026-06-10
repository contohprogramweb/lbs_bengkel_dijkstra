-- Migration: Remove redundant completed_at from booking_mechanics table
-- Reason: completed_at already exists in bookings table (single source of truth)
-- Date: 2026-06-07

-- Step 1: Remove completed_at column from booking_mechanics table
ALTER TABLE `booking_mechanics` DROP COLUMN IF EXISTS `completed_at`;

-- Note: All queries should now use bookings.completed_at instead of booking_mechanics.completed_at
-- The bookings table is the authoritative source for booking completion timestamp
