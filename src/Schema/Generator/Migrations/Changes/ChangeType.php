<?php

declare(strict_types=1);

namespace Arcano\Schema\Generator\Migrations\Changes;

/**
 * @internal
 */
enum ChangeType
{
    case CreateTable;
    case DropTable;
    case RenameTable;
    case ChangeTable;
    case AddColumn;
    case DropColumn;
    case AlterColumn;
    case AddIndex;
    case DropIndex;
    case AlterIndex;
    case AddFk;
    case DropFk;
    case AlterFk;
}
