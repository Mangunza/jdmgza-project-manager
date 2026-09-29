import { createBrowserRouter } from "react-router-dom";

import Home from "../pages/Home";
import Dashboard from "../pages/Dashboard";
import NotFound from "../pages/NotFound";

import MainLayout from "../layouts/MainLayout";
import DashboardLayout from "../layouts/DashboardLayout";

import RequireAuth from "./auth/RequireAuth";

import {
  ProjectDetailPage,
  ProjectNewPage,
  ProjectsPage,
} from "../features/projects";
import Products from "../features/products/Products";
import ResetPasswordPage from "../pages/auth/ResetPasswordPage/ResetPasswordPage";
import ForgotPasswordPage from "../pages/auth/ForgotPasswordPage/ForgotPassword";
import RegisterPage from "../pages/auth/RegisterPage/RegisterPage";
import LoginPage from "../pages/auth/LoginPage/LoginPage";

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
            path: "/products",
            element: <Products />,
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
