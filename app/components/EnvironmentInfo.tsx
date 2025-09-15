"use client";

import { env, getEnvironmentInfo } from '../../lib/env';

interface EnvironmentInfoProps {
  showInProduction?: boolean;
}

export default function EnvironmentInfo({ showInProduction = false }: EnvironmentInfoProps) {
  // Only show in development or if explicitly enabled
  if (env.IS_PRODUCTION && !showInProduction) {
    return null;
  }

  const info = getEnvironmentInfo();

  return (
    <div className="fixed bottom-4 right-4 bg-gray-900 text-white p-3 rounded-lg text-xs font-mono z-50 opacity-75 hover:opacity-100 transition-opacity">
      <div className="font-bold text-green-400 mb-1">Environment Info</div>
      <div>Mode: <span className="text-yellow-400">{info.current}</span></div>
      <div>API: <span className="text-blue-400">{info.apiUrl}</span></div>
      <div>App: <span className="text-purple-400">{info.appName}</span></div>
      <div>Version: <span className="text-cyan-400">{info.version}</span></div>
      <div>Debug: <span className={info.debug ? 'text-green-400' : 'text-red-400'}>{info.debug ? 'ON' : 'OFF'}</span></div>
      <div>Analytics: <span className={info.analytics ? 'text-green-400' : 'text-red-400'}>{info.analytics ? 'ON' : 'OFF'}</span></div>
    </div>
  );
}
