import { LandingPage } from "@/features/landing/views/landing-page";

export default function LandingRoute() {
  return <LandingPage currentYear={new Date().getFullYear()} />;
}
