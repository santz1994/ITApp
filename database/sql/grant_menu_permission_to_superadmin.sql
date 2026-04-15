-- Grant manage-menus permission to super-admin role
INSERT INTO permission_role (permission_id, role_id) 
SELECT 98, 2
WHERE NOT EXISTS (
    SELECT 1 FROM permission_role 
    WHERE permission_id = 98 AND role_id = 2
);
