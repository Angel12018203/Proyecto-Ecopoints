import React, { useEffect } from 'react';
import { CheckCircleIcon, XIcon } from './Icons';

export interface ToastData {
  id: string;
  type: 'success' | 'error' | 'info';
  message: string;
}

interface ToastProps {
  toast: ToastData | null;
  onClose: () => void;
}

export const Toast: React.FC<ToastProps> = ({ toast, onClose }) => {
  useEffect(() => {
    if (!toast) return;
    const timer = setTimeout(() => {
      onClose();
    }, 4500);
    return () => clearTimeout(timer);
  }, [toast, onClose]);

  if (!toast) return null;

  const isSuccess = toast.type === 'success';
  const isError = toast.type === 'error';

  return (
    <div className="fixed bottom-6 right-6 z-50 max-w-md w-full animate-bounce-short">
      <div
        className={`flex items-start gap-3 p-4 rounded-2xl shadow-xl border backdrop-blur-md ${
          isSuccess
            ? 'bg-[#EEF8EE]/95 border-[#087A3D]/30 text-[#087A3D]'
            : isError
            ? 'bg-red-50/95 border-red-300 text-red-800'
            : 'bg-white/95 border-slate-200 text-slate-800'
        }`}
      >
        <div className="shrink-0 mt-0.5">
          {isSuccess ? (
            <CheckCircleIcon size={22} className="text-[#087A3D]" />
          ) : (
            <span className="text-xl">⚠️</span>
          )}
        </div>
        <div className="flex-1 text-sm font-medium leading-snug">
          {toast.message}
        </div>
        <button
          onClick={onClose}
          className="shrink-0 text-gray-400 hover:text-gray-700 transition-colors p-1"
        >
          <XIcon size={16} />
        </button>
      </div>
    </div>
  );
};
