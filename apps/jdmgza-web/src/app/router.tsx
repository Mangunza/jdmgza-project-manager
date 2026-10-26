import { createBrowserRouter } from "react-router-dom";

import Home from "../pages/Home";
import Dashboard from "../pages/Dashboard";
import NotFound from "../pages/NotFound";

import MainLayout from "../layouts/MainLayout";
import DashboardLayout from "../layouts/DashboardLayout";

import RequireAuth from "./auth/RequireAuth";
import RequirePermission from "./auth/RequirePermission";

import {
  ProjectDetailPage,
  ProjectNewPage,
  ProjectsPage,
} from "../features/projects";
import Products from "../features/products/Products";
import Services from "../pages/Services";
import ResetPasswordPage from "../pages/auth/ResetPasswordPage/ResetPasswordPage";
import ForgotPasswordPage from "../pages/auth/ForgotPasswordPage/ForgotPassword";
import RegisterPage from "../pages/auth/RegisterPage/RegisterPage";
import LoginPage from "../pages/auth/LoginPage/LoginPage";
import ServiceDetails from "../pages/Services/ServiceDetails";
import ServiceNew from "../pages/Services/ServiceNew";
import { UsersPage } from "../features/users";

export const router = createBrowserRouter([
  {
    element: <MainLayout />,
    children: [
      {
        path: "/",
        element: <Home />,
      },

      {
        path: "/login",
        element: <LoginPage />,
      },

      {
        path: "/register",
        element: <RegisterPage />,
      },

      {
        path: "/forgot-password",
        element: <ForgotPasswordPage />,
      },

      {
        path: "/reset-password",
        element: <ResetPasswordPage />,
      },
    ],
  },

  {
    element: <RequireAuth />,
    children: [
      {
        element: <DashboardLayout />,
        children: [
          {
            path: "/dashboard",
            element: <Dashboard />,
          },

          {
            element: (
              <RequirePermission
                permission="users.view"
                redirectTo="/dashboard"
              />
            ),
            children: [
              {
                path: "/users",
                element: <UsersPage />,
              },
            ],
          },

          {
            element: (
              <RequirePermission
                permission="products.view"
                redirectTo="/dashboard"
              />
            ),
            children: [
              {
                path: "/products",
                element: <Products />,
              },
            ],
          },

          {
            element: (
              <RequirePermission
                permission="services.view"
                redirectTo="/dashboard"
              />
            ),
            children: [
              {
                path: "/services",
                element: <Services />,
              },

              {
                element: (
                  <RequirePermission
                    permission="services.create"
                    redirectTo="/services"
                  />
                ),
                children: [
                  {
                    path: "/services/new",
                    element: <ServiceNew />,
                  },
                ],
              },

              {
                path: "/services/:serviceId",
                element: <ServiceDetails />,
              },
            ],
          },

          {
            path: "/projects",
            element: <ProjectsPage />,
          },

          {
            path: "/projects/new",
            element: <ProjectNewPage />,
          },

          {
            path: "/projects/:projectId",
            element: <ProjectDetailPage />,
          },
        ],
      },
    ],
  },

  {
    path: "*",
    element: <NotFound />,
  },
]);
