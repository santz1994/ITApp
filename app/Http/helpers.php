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
          if (!is_object($user) || !method_exists($user, 'hasAnyRole')) {
            return false;
          }

          $expanded = \App\Role::equivalentNames((string) $role);
          return $user->hasAnyRole($expanded);
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
          if (!is_object($user) || !method_exists($user, 'hasAnyRole')) {
            return false;
          }

          $list = is_array($roles) ? $roles : [$roles];
          $normalizedInput = array_map(static function ($roleName): string {
            return (string) $roleName;
          }, $list);

          return $user->hasAnyRole(\App\Role::expandNames($normalizedInput));
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
