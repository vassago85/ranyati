<?php

return [

    /*
    | Where member documents and generated books are stored.
    | "auto"    — the private R2 bucket when it is configured, otherwise the
    |             private local disk (storage/app/private).
    | "custody" — always R2.
    | "local"   — always the private local disk.
    */
    'storage' => env('MOTIVATIONS_STORAGE', 'auto'),

    /*
    | Ghostscript binary used to rewrite uploaded PDFs that FPDI cannot parse
    | (PDF 1.5+ compressed cross-reference streams). "gs" on Linux, usually
    | "gswin64c" on Windows. When it is missing, the document gets a "print
    | separately" page in the book instead.
    */
    'ghostscript_binary' => env('GHOSTSCRIPT_BINARY', 'gs'),

    /* Maximum upload size per document, in kilobytes. */
    'max_upload_kb' => (int) env('MOTIVATIONS_MAX_UPLOAD_KB', 15360),

    /*
    | Ranyati inbox that receives internal notifications (new submission,
    | proof of payment uploaded). Falls back to MAIL_FROM_ADDRESS so the
    | system never fails to send if the env variable is missing.
    */
    'inbox' => env('MOTIVATIONS_INBOX', env('MAIL_FROM_ADDRESS')),

    /* Public contact details that appear in member-facing mails. */
    'contact' => [
        'company' => env('MOTIVATIONS_COMPANY', 'Ranyati Firearm Motivations (Pty) Ltd'),
        'phone' => env('MOTIVATIONS_PHONE'),
        'email' => env('MOTIVATIONS_PUBLIC_EMAIL', env('MOTIVATIONS_INBOX')),
        'address' => env('MOTIVATIONS_ADDRESS'),
        'banking' => env('MOTIVATIONS_BANKING'),
    ],

    /*
    | How long a motivation may sit in its current admin-action status
    | before it goes amber (warning) or red (overdue) in the dashboard's
    | "Needs your action" table. The admin can tune these without a code
    | change by setting MOTIVATIONS_WAITING_AMBER_DAYS /
    | MOTIVATIONS_WAITING_RED_DAYS in `.env`.
    */
    'waiting_thresholds' => [
        'amber_days' => (int) env('MOTIVATIONS_WAITING_AMBER_DAYS', 3),
        'red_days' => (int) env('MOTIVATIONS_WAITING_RED_DAYS', 5),
    ],

];
