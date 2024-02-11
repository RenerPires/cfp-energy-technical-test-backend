<?php

namespace App\Enums;

enum PermissionTypes:string {
    case viewUsers = "view-users";
    case createUsers = "create-users";
    case updateUsers = "update-users";
    case deleteUsers = "delete-users";
    case inactivateUsers = "inactivate-users";
    case activateUsers = "activate-users";
    case grantPermissions = "grant-permissions";
    case revokePermissions = "revoke-permissions";
}
