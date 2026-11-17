import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  output: "standalone",
  serverExternalPackages: ["puppeteer"],
  outputFileTracingRoot: process.cwd(),
  async rewrites() {
    const apiBase = process.env.BASE_URI_API ?? "http://localhost:8000/api/v1";
    return [{ source: "/api/v1/:path*", destination: `${apiBase}/:path*` }];
  },
  images: {
    dangerouslyAllowSVG: true,
    contentDispositionType: "attachment",
    contentSecurityPolicy:
      "default-src 'self'; script-src 'none'; sandbox;",
  },
};

export default nextConfig;
