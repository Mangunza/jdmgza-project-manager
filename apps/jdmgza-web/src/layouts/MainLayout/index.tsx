import { Outlet, Link } from "react-router-dom";

export default function MainLayout() {
  return (
    <>
      <header>
        <h1>Jdmgza Project Manager</h1>

        <nav>
          <Link to="/">Home</Link>
          {" | "}
          <Link to="/login">Login</Link>
          {" | "}
          <Link to="/dashboard">Dashboard</Link>
          {" | "}
          <Link to="/products">Products</Link>
        </nav>
      </header>

      <main>
        <Outlet />
      </main>
    </>
  );
}
