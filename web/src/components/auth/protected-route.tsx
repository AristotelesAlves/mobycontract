"use client";

import { useEffect } from "react";
import { useRouter, usePathname } from "next/navigation";
import { useAuth } from "@/lib/auth-context";

interface ProtectedRouteProps {
  children: React.ReactNode;
  allowedRoles?: string[];
}

export function ProtectedRoute({ children, allowedRoles }: ProtectedRouteProps) {
  const { user, isLoading } = useAuth();
  const router = useRouter();
  const pathname = usePathname();

  useEffect(() => {
    if (isLoading) return;

    if (!user) {
      const loginUrl = new URL("/login", window.location.origin);
      loginUrl.searchParams.set("callbackUrl", pathname);
      router.push(loginUrl.toString());
      return;
    }

    if (allowedRoles && !allowedRoles.includes(user.papel)) {
      router.push("/dashboard");
    }
  }, [isLoading, user, allowedRoles, router, pathname]);

  if (isLoading) {
    return (
      <div className="flex min-h-screen items-center justify-center">
        <p className="text-muted-foreground">Carregando...</p>
      </div>
    );
  }

  if (!user) {
    return null;
  }

  if (allowedRoles && user && !allowedRoles.includes(user.papel)) {
    return null;
  }

  return <>{children}</>;
}
