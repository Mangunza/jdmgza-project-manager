import { useEffect, useMemo, useState } from "react";
import { getProductCategories, getProducts } from "@jm/api";
import { useAuth } from "@jm/auth";
import type { Product, ProductCategory } from "@jm/types";
import { ErrorState, Input, LoadingState, Select } from "@jm/ui";

import "./products.css";

type StatusFilter = "all" | "active" | "inactive";

function formatPrice(value: string): string {
  return new Intl.NumberFormat("pt-AO", {
    style: "currency",
    currency: "AOA",
  }).format(Number(value));
}

export default function Products() {
  const { hasPermission } = useAuth();
  const [products, setProducts] = useState<Product[]>([]);
  const [categories, setCategories] = useState<ProductCategory[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [search, setSearch] = useState("");
  const [status, setStatus] = useState<StatusFilter>("all");
  const [categoryId, setCategoryId] = useState("");
  const canViewInactive = hasPermission("products.view_inactive");

  async function load(): Promise<void> {
    setLoading(true);
    setError(null);
    try {
      const [nextProducts, nextCategories] = await Promise.all([
        getProducts({ include_inactive: canViewInactive }),
        getProductCategories(),
      ]);
      setProducts(nextProducts);
      setCategories(nextCategories);
    } catch (cause) {
      setError(cause instanceof Error ? cause.message : "Não foi possível carregar os produtos.");
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => { void load(); }, [canViewInactive]);

  const filteredProducts = useMemo(() => {
    const term = search.trim().toLocaleLowerCase();
    return products.filter((product) =>
      (status === "all" || (status === "active" ? product.is_active : !product.is_active)) &&
      (!categoryId || product.category.id === Number(categoryId)) &&
      (!term || `${product.name} ${product.description ?? ""}`.toLocaleLowerCase().includes(term)),
    );
  }, [categoryId, products, search, status]);

  if (loading) return <LoadingState message="A carregar produtos..." />;
  if (error) return <ErrorState message={error} onRetry={() => void load()} />;

  return <section className="products-page">
    <header className="products-header"><div><p>Catálogo</p><h1>Produtos</h1><span>Produtos ativos e inventário disponível.</span></div></header>
    <div className="products-filters">
      <Input type="search" value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Pesquisar por nome ou descrição" aria-label="Pesquisar produtos" />
      <Select value={categoryId} onChange={(event) => setCategoryId(event.target.value)} aria-label="Filtrar por categoria"><option value="">Todas as categorias</option>{categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}</Select>
      <Select value={status} onChange={(event) => setStatus(event.target.value as StatusFilter)} aria-label="Filtrar por estado"><option value="all">Todos os estados</option><option value="active">Ativos</option>{canViewInactive && <option value="inactive">Inativos</option>}</Select>
    </div>
    {filteredProducts.length === 0 ? <p className="products-empty">Nenhum produto encontrado para estes filtros.</p> : <div className="products-grid">{filteredProducts.map((product) => <article className="product-card" key={product.id}>{product.primary_image_url ? <img src={product.primary_image_url} alt="" className="product-card-image" /> : <div className="product-card-image product-card-placeholder" aria-label="Sem imagem">Sem imagem</div>}<div><span className={product.is_active ? "product-status" : "product-status product-status-inactive"}>{product.is_active ? "Ativo" : "Inativo"}</span><h2>{product.name}</h2><p>{product.category.name}</p><strong>{formatPrice(product.price)}</strong><p>Stock: {product.stock}</p></div></article>)}</div>}
  </section>;
}
