
CREATE TABLE hospitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    serial_number INT AUTO_INCREMENT UNIQUE,
    name_en VARCHAR(255) NOT NULL,
    name_hi VARCHAR(255) NOT NULL,
    address_en TEXT NOT NULL,
    address_hi TEXT NOT NULL,
    state VARCHAR(100) NOT NULL,
    payment_scheme ENUM('Direct', 'Non-Direct') NOT NULL,
    contact_person VARCHAR(100) NOT NULL,
    contact_number VARCHAR(15) NOT NULL,
    valid_from DATE,
    valid_upto DATE,
    reg_valid_upto DATE,
    remarks_en TEXT,
    remarks_hi TEXT,
    approv_order_accomodation TEXT,
    approv_order_doc VARCHAR(255),
    tariff TEXT,
    tariff_doc VARCHAR(255),
    facilitation TEXT,
    facilitation_doc VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- List of Indian states for dropdown
CREATE TABLE states (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- Insert Indian states
INSERT INTO states (name) VALUES
('Andhra Pradesh'), ('Arunachal Pradesh'), ('Assam'), ('Bihar'),
('Chhattisgarh'), ('Goa'), ('Gujarat'), ('Haryana'), ('Himachal Pradesh'),
('Jharkhand'), ('Karnataka'), ('Kerala'), ('Madhya Pradesh'), ('Maharashtra'),
('Manipur'), ('Meghalaya'), ('Mizoram'), ('Nagaland'), ('Odisha'),
('Punjab'), ('Rajasthan'), ('Sikkim'), ('Tamil Nadu'), ('Telangana'),
('Tripura'), ('Uttar Pradesh'), ('Uttarakhand'), ('West Bengal'),
('Andaman and Nicobar Islands'), ('Chandigarh'), ('Dadra and Nagar Haveli and Daman and Diu'),
('Delhi'), ('Jammu and Kashmir'), ('Ladakh'), ('Lakshadweep'), ('Puducherry');
