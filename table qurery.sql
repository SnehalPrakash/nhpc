
CREATE TABLE hospitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    serial_number INT AUTO_INCREMENT UNIQUE,
    name_en VARCHAR(255),
    name_hi VARCHAR(255),
    address_en TEXT,
    address_hi TEXT,
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
