import type { HTMLAttributes } from "react";

export type AlertVariant = "info" | "success" | "warning" | "danger";

export interface AlertProps
  extends HTMLAttributes<HTMLParagraphElement> {
  variant?: AlertVariant;
}

export function Alert({
  role = "alert",
  variant = "info",
  className = "",
  ...props
}: AlertProps) {
  const classes = [
    "jm-alert",
    `jm-alert--${variant}`,
    className,
  ]
    .filter(Boolean)
    .join(" ");

  return <p role={role} className={classes} {...props} />;
}
