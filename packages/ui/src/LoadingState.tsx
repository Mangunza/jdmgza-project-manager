export interface LoadingStateProps {
  message?: string;
}

export function LoadingState({
  message = "A carregar...",
}: LoadingStateProps) {
  return (
    <section
      className="jm-loading-state"
      role="status"
      aria-live="polite"
    >
      <span
        className="jm-loading-state__spinner"
        aria-hidden="true"
      />

      <p className="jm-loading-state__message">
        {message}
      </p>
    </section>
  );
}
