"use client";

import { createContext, useContext, useEffect, useState, ReactNode } from "react";
import { api } from "@/services/api/base-api";

interface User {
  id: number;
  nome: string;
  email: string;
  papel: string;
  ativo: boolean;
}

interface AuthContextType {
  user: User | null;
  isLoading: boolean;
  login: (email: string, senha: string) => Promise<void>;
  logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

const LEGACY_AUTH_STORAGE_KEYS = [
  "mobicontract:access_token",
  "mobicontract:refresh_token",
  "mobicontract:user",
];

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const initAuth = async () => {
      LEGACY_AUTH_STORAGE_KEYS.forEach((key) => localStorage.removeItem(key));

      try {
        const response = await api.get<{ dados: User }>("/usuarios/sessao");
        setUser(response.dados);
      } catch {
        clearAuth();
      } finally {
        setIsLoading(false);
      }
    };
    initAuth();
  }, []);

  const clearAuth = () => {
    setUser(null);
  };

  const login = async (email: string, senha: string) => {
    const response = await api.post<{
      dados: {
        usuario: User;
      };
    }>("/usuarios/sessao/entrar", { email, senha });

    const { usuario } = response.dados;
    setUser(usuario);
  };

  const logout = async () => {
    try {
      await api.delete("/usuarios/sessao");
    } finally {
      clearAuth();
    }
  };

  return (
    <AuthContext.Provider value={{ user, isLoading, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) throw new Error("useAuth must be used within AuthProvider");
  return context;
}
