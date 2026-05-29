import { useDispatch } from 'react-redux';
import { Link } from 'react-router-dom';
import { logout } from '../../store/slices/authSlice';

function Header({ user }) {
    const dispatch = useDispatch();

    const handleLogout = () => {
        dispatch(logout());
    };

    return (
        <header className="main-header">
            <Link to="/" className="logo">
                <span className="logo-lg"><b>IT</b>App</span>
                <span className="logo-mini"><b>IT</b>A</span>
            </Link>
            <nav className="navbar navbar-static-top">
                <a href="#" className="sidebar-toggle" data-toggle="push-menu" role="button">
                    <span className="sr-only">Toggle navigation</span>
                </a>
                <div className="navbar-custom-menu">
                    <ul className="nav navbar-nav">
                        <li className="dropdown user user-menu">
                            <a href="#" className="dropdown-toggle" data-toggle="dropdown">
                                <span className="hidden-xs">{user?.name || 'User'}</span>
                            </a>
                            <ul className="dropdown-menu">
                                <li className="user-header">
                                    <p>{user?.name || 'User'}<small>{user?.email || ''}</small></p>
                                </li>
                                <li className="user-footer">
                                    <button onClick={handleLogout} className="btn btn-default btn-flat btn-block">
                                        <i className="fa fa-sign-out"></i> Logout
                                    </button>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
    );
}

export default Header;