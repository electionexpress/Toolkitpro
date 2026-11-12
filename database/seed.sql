-- ToolkitPro starter categories. Safe to run more than once.
INSERT INTO categories (slug, name_bn, name_en, sort_order, is_active) VALUES
('ai-tools', 'এআই টুলস', 'AI Tools', 10, TRUE),
('pdf-tools', 'পিডিএফ টুলস', 'PDF Tools', 20, TRUE),
('image-tools', 'ইমেজ টুলস', 'Image Tools', 30, TRUE),
('calculators', 'ক্যালকুলেটর', 'Calculators', 40, TRUE),
('text-tools', 'টেক্সট টুলস', 'Text Tools', 50, TRUE),
('developer-tools', 'ডেভেলপার টুলস', 'Developer Tools', 60, TRUE),
('converters', 'ফাইল কনভার্টার', 'File Converters', 70, TRUE),
('security-tools', 'নিরাপত্তা টুলস', 'Security Tools', 80, TRUE),
('finance-tools', 'ফিন্যান্স টুলস', 'Finance Tools', 90, TRUE),
('education-tools', 'শিক্ষা টুলস', 'Education Tools', 100, TRUE),
('productivity', 'প্রোডাক্টিভিটি', 'Productivity', 110, TRUE),
('web-tools', 'ওয়েব টুলস', 'Web Tools', 120, TRUE)
ON CONFLICT (slug) DO UPDATE SET
    name_bn = EXCLUDED.name_bn,
    name_en = EXCLUDED.name_en,
    sort_order = EXCLUDED.sort_order,
    is_active = EXCLUDED.is_active;
