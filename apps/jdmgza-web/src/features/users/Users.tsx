import { useCallback, useEffect, useMemo, useState } from "react";
import type { FormEvent } from "react";

import {
  createUser,
  deleteUser,
  getUsers,
  updateUser,
} from "@jm/api";
import { useAuth } from "@jm/auth";
import type {
  CreateUserPayload,
  UpdateUserPayload,
  User,
} from "@jm/types";
import {
  Alert,
  Button,
  ErrorState,
  Input,
  LoadingState,
} from "@jm/ui";

import "./users.css";

interface UserFormState {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

const EMPTY_FORM: UserFormState = {
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
};

function getErrorMessage(error: unknown): string {
  return error instanceof Error
    ? error.message
    : "Ocorreu um erro inesperado. Tenta novamente.";
}

function formatDate(value: string): string {
  if (!value) return "—";

  const date = new Date(value);

  if (Number.isNaN(date.getTime())) return "—";

  return new Intl.DateTimeFormat("pt-AO", {
    dateStyle: "medium",
  }).format(date);
}

export default function Users() {
  const { hasPermission } = useAuth();

  const canCreate = hasPermission("users.create");
  const canUpdate = hasPermission("users.update");
  const canDelete = hasPermission("users.delete");

  const [users, setUsers] = useState<User[]>([]);
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [total, setTotal] = useState(0);

  const [search, setSearch] = useState("");
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [deletingId, setDeletingId] = useState<number | null>(null);

  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const [formError, setFormError] = useState<string | null>(null);

  const [formOpen, setFormOpen] = useState(false);
  const [editingUser, setEditingUser] = useState<User | null>(null);
  const [form, setForm] = useState<UserFormState>(EMPTY_FORM);

  const loadUsers = useCallback(async () => {
    setLoading(true);
    setError(null);

    try {
      const response = await getUsers(page);

      setUsers(response.data);
      setLastPage(response.meta.last_page);
      setTotal(response.meta.total);
    } catch (cause) {
      setError(getErrorMessage(cause));
    } finally {
      setLoading(false);
    }
  }, [page]);

  useEffect(() => {
    void loadUsers();
  }, [loadUsers]);

  const filteredUsers = useMemo(() => {
    const term = search.trim().toLocaleLowerCase();

    if (!term) return users;

    return users.filter((user) =>
      `${user.name} ${user.email} ${(user.roles ?? []).join(" ")}`
        .toLocaleLowerCase()
        .includes(term),
    );
  }, [search, users]);

  function openCreateForm() {
    setEditingUser(null);
    setForm(EMPTY_FORM);
    setFormError(null);
    setSuccess(null);
    setFormOpen(true);
  }

  function openEditForm(user: User) {
    setEditingUser(user);
    setForm({
      name: user.name,
      email: user.email,
      password: "",
      password_confirmation: "",
    });
    setFormError(null);
    setSuccess(null);
    setFormOpen(true);
  }

  function closeForm() {
    if (saving) return;

    setFormOpen(false);
    setEditingUser(null);
    setForm(EMPTY_FORM);
    setFormError(null);
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setFormError(null);
    setSuccess(null);

    if (!form.name.trim() || !form.email.trim()) {
      setFormError("Preenche o nome e o e-mail.");
      return;
    }

    if (!editingUser && form.password.length < 8) {
      setFormError("A palavra-passe deve ter pelo menos 8 caracteres.");
      return;
    }

    if (form.password || !editingUser) {
      if (form.password !== form.password_confirmation) {
        setFormError("As palavras-passe não coincidem.");
        return;
      }
    }

    setSaving(true);

    try {
      if (editingUser) {
        const payload: UpdateUserPayload = {
          name: form.name.trim(),
          email: form.email.trim(),
        };

        if (form.password) {
          payload.password = form.password;
          payload.password_confirmation = form.password_confirmation;
        }

        const updatedUser = await updateUser(editingUser.id, payload);

        setUsers((current) =>
          current.map((user) =>
            user.id === updatedUser.id ? updatedUser : user,
          ),
        );

        setSuccess("Utilizador actualizado com sucesso.");
      } else {
        const payload: CreateUserPayload = {
          name: form.name.trim(),
          email: form.email.trim(),
          password: form.password,
          password_confirmation: form.password_confirmation,
        };

        await createUser(payload);

        setPage(1);
        setSearch("");
        setSuccess("Utilizador criado com sucesso.");
      }

      setFormOpen(false);
      setEditingUser(null);
      setForm(EMPTY_FORM);

      if (!editingUser && page === 1) {
        await loadUsers();
      }
    } catch (cause) {
      setFormError(getErrorMessage(cause));
    } finally {
      setSaving(false);
    }
  }

  async function handleDelete(user: User) {
    const confirmed = window.confirm(
      `Tens a certeza de que pretendes eliminar o utilizador "${user.name}"? Esta operação não pode ser desfeita.`,
    );

    if (!confirmed) return;

    setDeletingId(user.id);
    setError(null);
    setSuccess(null);

    try {
      await deleteUser(user.id);

      if (users.length === 1 && page > 1) {
        setPage((current) => current - 1);
      } else {
        await loadUsers();
      }

      setSuccess("Utilizador eliminado com sucesso.");
    } catch (cause) {
      setError(getErrorMessage(cause));
    } finally {
      setDeletingId(null);
    }
  }

  if (loading && users.length === 0) {
    return <LoadingState message="A carregar utilizadores..." />;
  }

  if (error && users.length === 0) {
    return (
      <ErrorState
        message={error}
        onRetry={() => void loadUsers()}
      />
    );
  }

  return (
    <section className="users-page">
      <header className="users-header">
        <div>
          <p className="users-eyebrow">Administração</p>
          <h1>Utilizadores</h1>
          <p className="users-description">
            Gere as contas de acesso à plataforma.
          </p>
        </div>

        {canCreate && (
          <Button onClick={openCreateForm}>
            Novo utilizador
          </Button>
        )}
      </header>

      <div className="users-summary">
        <div className="users-summary-card">
          <span>Total de utilizadores</span>
          <strong>{total}</strong>
        </div>

        <div className="users-summary-card">
          <span>Página actual</span>
          <strong>{page} / {lastPage}</strong>
        </div>
      </div>

      {error && (
        <Alert variant="danger">
          {error}
        </Alert>
      )}

      {success && (
        <Alert variant="success">
          {success}
        </Alert>
      )}

      {formOpen && (
        <section className="users-form-panel">
          <div className="users-form-heading">
            <div>
              <h2>
                {editingUser ? "Editar utilizador" : "Novo utilizador"}
              </h2>
              <p>
                {editingUser
                  ? "Actualiza os dados da conta."
                  : "Cria uma conta para aceder à plataforma."}
              </p>
            </div>

            <Button
              type="button"
              variant="ghost"
              onClick={closeForm}
              disabled={saving}
              aria-label="Fechar formulário"
            >
              Fechar
            </Button>
          </div>

          {formError && (
            <Alert variant="danger">
              {formError}
            </Alert>
          )}

          <form className="users-form" onSubmit={handleSubmit}>
            <label>
              Nome completo
              <Input
                value={form.name}
                onChange={(event) =>
                  setForm({ ...form, name: event.target.value })
                }
                autoComplete="name"
                maxLength={255}
                required
              />
            </label>

            <label>
              E-mail
              <Input
                type="email"
                value={form.email}
                onChange={(event) =>
                  setForm({ ...form, email: event.target.value })
                }
                autoComplete="email"
                maxLength={255}
                required
              />
            </label>

            <label>
              {editingUser
                ? "Nova palavra-passe (opcional)"
                : "Palavra-passe"}
              <Input
                type="password"
                value={form.password}
                onChange={(event) =>
                  setForm({ ...form, password: event.target.value })
                }
                autoComplete={
                  editingUser ? "new-password" : "new-password"
                }
                minLength={8}
                required={!editingUser}
              />
            </label>

            <label>
              Confirmar palavra-passe
              <Input
                type="password"
                value={form.password_confirmation}
                onChange={(event) =>
                  setForm({
                    ...form,
                    password_confirmation: event.target.value,
                  })
                }
                autoComplete="new-password"
                minLength={8}
                required={!editingUser || Boolean(form.password)}
              />
            </label>

            {!editingUser && (
              <p className="users-form-note">
                A conta será criada com o papel predefinido pelo backend.
                A atribuição de outros papéis não está incluída neste formulário.
              </p>
            )}

            <div className="users-form-actions">
              <Button
                type="button"
                variant="outline"
                onClick={closeForm}
                disabled={saving}
              >
                Cancelar
              </Button>

              <Button type="submit" disabled={saving}>
                {saving
                  ? "A guardar..."
                  : editingUser
                    ? "Guardar alterações"
                    : "Criar utilizador"}
              </Button>
            </div>
          </form>
        </section>
      )}

      <div className="users-toolbar">
        <Input
          type="search"
          value={search}
          onChange={(event) => setSearch(event.target.value)}
          placeholder="Pesquisar nesta página..."
          aria-label="Pesquisar utilizadores"
        />

        <span>
          {filteredUsers.length} resultado(s) nesta página
        </span>
      </div>

      {loading && users.length > 0 && (
        <LoadingState message="A actualizar a lista..." />
      )}

      {!loading && filteredUsers.length === 0 ? (
        <div className="users-empty">
          <h2>Nenhum utilizador encontrado</h2>
          <p>
            {search
              ? "Experimenta pesquisar por outro nome ou e-mail."
              : "Ainda não existem utilizadores nesta página."}
          </p>
        </div>
      ) : (
        <div className="users-table-wrapper">
          <table className="users-table">
            <thead>
              <tr>
                <th>Utilizador</th>
                <th>Papel</th>
                <th>Data de criação</th>
                {(canUpdate || canDelete) && <th>Acções</th>}
              </tr>
            </thead>

            <tbody>
              {filteredUsers.map((user) => (
                <tr key={user.id}>
                  <td>
                    <div className="users-identity">
                      <span className="users-avatar" aria-hidden="true">
                        {user.name.trim().charAt(0).toLocaleUpperCase() || "?"}
                      </span>

                      <div>
                        <strong>{user.name}</strong>
                        <span>{user.email}</span>
                      </div>
                    </div>
                  </td>

                  <td>
                    {user.roles?.length
                      ? user.roles.join(", ")
                      : "Não disponibilizado"}
                  </td>

                  <td>{formatDate(user.created_at)}</td>

                  {(canUpdate || canDelete) && (
                    <td>
                      <div className="users-row-actions">
                        {canUpdate && (
                          <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={() => openEditForm(user)}
                          >
                            Editar
                          </Button>
                        )}

                        {canDelete && (
                          <Button
                            type="button"
                            variant="danger"
                            size="sm"
                            disabled={deletingId === user.id}
                            onClick={() => void handleDelete(user)}
                          >
                            {deletingId === user.id
                              ? "A eliminar..."
                              : "Eliminar"}
                          </Button>
                        )}
                      </div>
                    </td>
                  )}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <footer className="users-pagination">
        <span>
          Página {page} de {lastPage}
        </span>

        <div>
          <Button
            type="button"
            variant="outline"
            disabled={page <= 1 || loading}
            onClick={() => setPage((current) => current - 1)}
          >
            Anterior
          </Button>

          <Button
            type="button"
            variant="outline"
            disabled={page >= lastPage || loading}
            onClick={() => setPage((current) => current + 1)}
          >
            Seguinte
          </Button>
        </div>
      </footer>
    </section>
  );
}
