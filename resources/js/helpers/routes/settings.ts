/**
 * Settings Routes Helper
 * Clean wrapper untuk settings-related route actions
 */

// Re-export settings controllers dengan nama yang lebih clean
export { default as profileActions } from '@/actions/App/Http/Controllers/Settings/ProfileController';
export { default as passwordActions } from '@/actions/App/Http/Controllers/Settings/PasswordController';