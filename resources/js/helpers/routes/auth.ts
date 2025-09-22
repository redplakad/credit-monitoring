/**
 * Auth Routes Helper
 * Clean wrapper untuk auth-related route actions
 */

// Re-export auth controllers dengan nama yang lebih clean
export { default as registerActions } from '@/actions/App/Http/Controllers/Auth/RegisteredUserController';
export { default as loginActions } from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
export { default as passwordResetActions } from '@/actions/App/Http/Controllers/Auth/PasswordResetLinkController';
export { default as newPasswordActions } from '@/actions/App/Http/Controllers/Auth/NewPasswordController';
export { default as emailVerificationActions } from '@/actions/App/Http/Controllers/Auth/VerifyEmailController';