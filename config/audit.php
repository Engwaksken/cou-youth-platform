<?php

return [
    /*
    | Whole calendar months of activity kept in the database. Older months are
    | moved to compressed archive files by the monthly `audit:archive` job.
    */
    'retention_months' => max(1, (int) env('AUDIT_LOG_RETENTION_MONTHS', 3)),

    // Directory on the private "local" disk where monthly archives are written.
    'archive_directory' => 'audit-archives',
];
