import { useSelector } from 'react-redux';
import { Link, useLocation } from 'react-router-dom';

const menuItems = [
    { path: '/', icon: 'fa-tachometer-alt', label: 'Dashboard' },
    { section: 'Fasilitas' },
    { path: '/vehicles', icon: 'fa-car', label: 'Kendaraan' },
    { path: '/vehicle-bookings/my', icon: 'fa-calendar-alt', label: 'Booking Saya' },
    { section: 'Inventaris' },
    { path: '/inventory', icon: 'fa-cubes', label: 'ATK & Sparepart' },
    { path: '/inventory-requests', icon: 'fa-file-alt', label: 'Request Inventaris' },
    { section: 'Approval' },
    { path: '/approvals', icon: 'fa-check-double', label: 'Pending Approvals' },
];

function Sidebar({ user }) {
    const location = useLocation();
    const { pendingApprovals } = useSelector((state) => state.approvals);

    return (
        <aside className="main-sidebar">
            <section className="sidebar">
                <div className="user-panel">
                    <div className="pull-left image">
                        <div className="bg-aqua text-white circle" style={{ width: 45, height: 45, display: 'flex', alignItems: 'center', justifyContent: 'center', borderRadius: '50%', fontSize: 18 }}>
                            {user?.name?.charAt(0)?.toUpperCase() || 'U'}
                        </div>
                    </div>
                    <div className="pull-left info">
                        <p>{user?.name || 'User'}</p>
                        <a href="#"><i className="fa fa-circle text-success"></i> Online</a>
                    </div>
                </div>

                <ul className="sidebar-menu" data-widget="tree">
                    {menuItems.map((item, index) => {
                        if (item.section) {
                            return <li key={index} className="header">{item.section}</li>;
                        }
                        const isActive = location.pathname === item.path || 
                            (item.path !== '/' && location.pathname.startsWith(item.path));
                        return (
                            <li key={index} className={isActive ? 'active' : ''}>
                                <Link to={item.path}>
                                    <i className={`fa ${item.icon}`}></i>
                                    <span>{item.label}</span>
                                    {item.path === '/approvals' && pendingApprovals.length > 0 && (
                                        <span className="pull-right-container">
                                            <small className="label pull-right bg-red">{pendingApprovals.length}</small>
                                        </span>
                                    )}
                                </Link>
                            </li>
                        );
                    })}
                </ul>
            </section>
        </aside>
    );
}

export default Sidebar;