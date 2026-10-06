import { Button } from "./Button";

export interface ErrorStateProps {
  message: string;
  onRetry?: () => void;
  retryLabel?: string;
}

export function ErrorState({
  message,
  onRetry,
  retryLabel = "Tentar novamente",
}: ErrorStateProps) {
  return (
    <section
      className="jm-error-state"
      role="alert"
      aria-live="polite"
    >
      <h2 className="jm-error-state__title">
        Ocorreu um erro
      </h2>

      <p className="jm-error-state__message">
        {message}
      </p>

      {onRetry ? (
        <Button
          type="button"
          variant="outline"
          onClick={onRetry}
        >
          {retryLabel}
        </Button>
      ) : null}
    </section>
  );
}
