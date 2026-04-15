<?php

  function hasErrorForClass($errors, $column) {
    if(count($errors)) {
      if ($errors->has($column)) {
        return 'has-error';
      }
    }
  }

  function hasErrorForField($errors, $column) {
    if(count($errors)) {
      if ($errors->has($column)) {
        print '<span class="help-block">' . $errors->first($column) . '</span>';
      }
    }
  }

  // Role-based access control helper functions
  // These functions wrap Spatie Laravel Permission methods to avoid IDE errors

  if (!function_exists('user_has_role')) {
      /**
       * Check if user has specific role
       *
       * @param mixed $user
       * @param string $role
       * @return bool
       */
      function user_has_role($user, $role)
      {
          return is_object($user) && method_exists($user, 'hasRole') ? $user->hasRole($role) : false;
      }
  }

  if (!function_exists('user_has_any_role')) {
      /**
       * Check if user has any of the specified roles
       *
       * @param mixed $user
       * @param array $roles
       * @return bool
       */
      function user_has_any_role($user, $roles)
      {
          return is_object($user) && method_exists($user, 'hasAnyRole') ? $user->hasAnyRole($roles) : false;
      }
  }

  if (!function_exists('user_get_role_names')) {
      /**
       * Get user role names
       *
       * @param mixed $user
       * @return \Illuminate\Support\Collection
       */
      function user_get_role_names($user)
      {
          if (is_object($user) && method_exists($user, 'getRoleNames')) {
              return $user->getRoleNames();
          }
          return collect();
      }
  }
