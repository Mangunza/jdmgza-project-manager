import { NavLink, Outlet } from "react-router-dom";

import { useAuth } from "@jm/auth";
import { Button } from "@jm/ui";

import "./styles.css";

const navigationItems = [
  { to: "/dashboard", label: "Dashboard" },
  { to: "/projects", label: "Projects" },
  { to: "/products", label: "Products" },
  { to: "/services", label: "Services" },
];

export default function DashboardLayout() {
  const { logout } = useAuth();

  return (
    <div className="dashboard-layout">
      <aside className="dashboard-sidebar">
        <div className="dashboard-brand">
          <span className="dashboard-brand__mark">JM</span>

          <div className="dashboard-brand__text">
            <strong>JM Project</strong>
            <span>Management</span>
          </div>
        </div>

        <nav
          className="dashboard-navigation"
          aria-label="Navegação principal"
        >
          <span className="dashboard-navigation__label">
            MENU
          </span>

          {navigationItems.map((item) => (
            <NavLink
              key={item.to}
              to={item.to}
              end={item.to === "/dashboard"}
              className={({ isActive }) =>
                [
                  "dashboard-navigation__link",
                  isActive
                    ? "dashboard-navigation__link--active"
                    : "",
                ]
                  .filter(Boolean)
                  .join(" ")
              }
            >
              {item.label}
            </NavLink>
          ))}
        </nav>

        <div className="dashboard-sidebar__footer">
          <span>JM Project Manager</span>
          <span>Workspace</span>
        </div>
      </aside>

      <div className="dashboard-main">
        <header className="dashboard-topbar">
          <div>
            <span className="dashboard-topbar__eyebrow">
              PROJECT MANAGEMENT
            </span>

            <p className="dashboard-topbar__title">
              Organize. Acompanhe. Entregue.
            </p>
          </div>

          <Button
            type="button"
            variant="ghost"
            onClick={() => {
              void logout();
            }}
          >
            Sair
          </Button>
        </header>

        <main className="dashboard-content">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
