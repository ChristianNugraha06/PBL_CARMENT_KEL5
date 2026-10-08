-- Sandi semua akun demo: password
INSERT INTO users (nama, email, hp, password, role) VALUES
('Customer Demo', 'customer@carment.id', '0800000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Staff Demo',    'staff@carment.id',    '0800000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff'),
('Owner Demo',    'owner@carment.id',    '0800000003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner')
ON CONFLICT (email) DO NOTHING;