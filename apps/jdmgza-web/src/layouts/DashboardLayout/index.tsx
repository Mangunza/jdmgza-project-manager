import { Link, Outlet } from "react-router-dom";

export default function DashboardLayout() {
  return (
    <div>
      <aside>
        <nav>
          <Link to="/dashboard">Dashboard</Link>
          {" | "}
          <Link to="/products">Products</Link>
        </nav>
      </aside>

      <main>
        <Outlet />
      </main>
    </div>
  );
}