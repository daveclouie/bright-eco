-- Sample data for testing and defense. Import AFTER schema.sql, on a fresh database.
USE bright_eco;

-- Accounts: owner / owner123 and staff / staff123 (admin / admin123 comes from schema.sql)
INSERT INTO users(id,name,username,password_hash,role) VALUES
(2,'Owner Account','owner','$2y$10$tnLbCbMiGLs.5rJAZWwMvefBDvMsiI5z3DCqJjBgzZmTlTjAAtDMq','owner'),
(3,'Staff Account','staff','$2y$10$9zg/dqqQ4prgdt9BgXQlQuJxjgsGUlGmyinPciiY/KOaH80qhILpK','staff');

-- Products (stock shown is AFTER the sample bills below). Category ids: 1 Panels, 2 Inverters, 3 Batteries, 4 Mounting, 5 Cables, 6 Charge Controllers
INSERT INTO products(id,name,category_id,specs,price,quantity,reorder_level) VALUES
(1,'550W Mono Solar Panel',1,'550W monocrystalline, 144 half-cut cells',8500.00,30,10),
(2,'450W Mono Solar Panel',1,'450W monocrystalline, 108 half-cut cells',6800.00,22,10),
(3,'Hybrid Inverter 5kW',2,'5kW, 48V, MPPT, with WiFi monitoring',48000.00,4,3),
(4,'Hybrid Inverter 3kW',2,'3kW, 24V, MPPT',32500.00,2,3),
(5,'Grid-Tie Inverter 5kW',2,'5kW, single phase, on-grid',38000.00,4,2),
(6,'LiFePO4 Battery 48V 100Ah',3,'48V 100Ah lithium iron phosphate, rack type',62000.00,5,3),
(7,'Deep Cycle Battery 12V 200Ah',3,'12V 200Ah gel, maintenance free',17500.00,3,4),
(8,'Aluminum Mounting Rail 4.2m',4,'Anodized aluminum, 4.2 meters',1250.00,56,20),
(9,'Roof Mounting Hook Set',4,'Stainless steel, 4 pieces per set',680.00,6,15),
(10,'PV Cable 4mm2 (per meter)',5,'Solar DC cable, UV resistant',45.00,240,100),
(11,'MC4 Connector Pair',5,'IP67 rated, 30A',120.00,148,50),
(12,'MPPT Charge Controller 60A',6,'12/24/48V auto, LCD display',9800.00,6,3);

-- Bills (ids 1 to 4) with different dates. Bills 3 and 4 are dated today so "Sales today" shows a value.
INSERT INTO bills(id,user_id,customer_name,subtotal,discount,total,created_at) VALUES
(1,2,'Maria Santos',105800.00,2000.00,103800.00,NOW() - INTERVAL 3 DAY),
(2,3,'Juan Dela Cruz',24540.00,0.00,24540.00,NOW() - INTERVAL 2 DAY),
(3,3,'Rodel Manalo',94500.00,0.00,94500.00,NOW()),
(4,2,'Elena Ramos',35360.00,1000.00,34360.00,NOW());

INSERT INTO bill_items(bill_id,product_id,quantity,unit_price) VALUES
(1,1,6,8500.00),(1,3,1,48000.00),(1,8,4,1250.00),(1,10,40,45.00),
(2,2,2,6800.00),(2,12,1,9800.00),(2,10,20,45.00),(2,11,2,120.00),
(3,6,1,62000.00),(3,4,1,32500.00),
(4,1,4,8500.00),(4,9,2,680.00);
