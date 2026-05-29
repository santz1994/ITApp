import { useSelector } from 'react-redux';
import { Outlet } from 'react-router-dom';
import Header from './Header';
import Sidebar from './Sidebar';

function Layout() {
    const { user } = useSelector((state) => state.auth);

    return (
        <div className="wrapper">
            <Header user={user} />
            <Sidebar user={user} />
            <div className="content-wrapper">
                <section className="content">
                    <Outlet />
                </section>
            </div>
        </div>
    );
}

export default Layout;