-- Add new columns to hospitals table
ALTER TABLE hospitals
ADD COLUMN state VARCHAR(100) NOT NULL DEFAULT '',
ADD COLUMN payment_scheme ENUM('Direct', 'Non-Direct') NOT NULL DEFAULT 'Direct',
ADD COLUMN contact_person VARCHAR(100) NOT NULL DEFAULT '',
ADD COLUMN contact_number VARCHAR(20) NOT NULL DEFAULT '';

-- Create index for faster state-wise filtering
CREATE INDEX idx_hospital_state ON hospitals(state);

-- Create index for faster payment scheme filtering
CREATE INDEX idx_payment_scheme ON hospitals(payment_scheme);