<?php

namespace App\Models;

use App\Support\Motivations\ConditionEvaluator;
use App\Support\Motivations\DocumentKinds;
use App\Support\Motivations\QuestionSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A member's application for one application type: their answers, uploaded
 * documents, and (once an admin confirms the details) the generated book.
 * The book is only ever available to admins — motivations are printed and
 * handed over, never sent electronically.
 */
class MotivationRequest extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_CHANGES_REQUESTED = 'changes_requested';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_AWAITING_ENDORSEMENT = 'awaiting_endorsement';

    public const STATUS_READY_TO_PRINT = 'ready_to_print';

    public const STATUS_PRINTED = 'printed';

    public const STATUS_INVOICED = 'invoiced';

    public const STATUS_PAID = 'paid';

    public const STATUS_READY_FOR_COLLECTION = 'ready_for_collection';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_SUBMITTED => 'Submitted',
        self::STATUS_CHANGES_REQUESTED => 'Changes requested',
        self::STATUS_VERIFIED => 'Details confirmed',
        self::STATUS_AWAITING_ENDORSEMENT => 'Awaiting endorsement',
        self::STATUS_READY_TO_PRINT => 'Ready to print',
        self::STATUS_PRINTED => 'Motivation printed',
        self::STATUS_INVOICED => 'Awaiting payment',
        self::STATUS_PAID => 'Payment received',
        self::STATUS_READY_FOR_COLLECTION => 'Ready for collection',
        self::STATUS_SHIPPED => 'Shipped',
        self::STATUS_COMPLETED => 'Completed',
    ];

    /** Member-facing status collapses the admin's internal workflow. */
    public const MEMBER_STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_SUBMITTED => 'Submitted, awaiting review',
        self::STATUS_CHANGES_REQUESTED => 'More information needed',
        self::STATUS_VERIFIED => 'Under review',
        self::STATUS_AWAITING_ENDORSEMENT => 'Under review',
        self::STATUS_READY_TO_PRINT => 'Under review',
        self::STATUS_PRINTED => 'Motivation completed',
        self::STATUS_INVOICED => 'Awaiting payment',
        self::STATUS_PAID => 'Preparing for delivery',
        self::STATUS_READY_FOR_COLLECTION => 'Ready for collection',
        self::STATUS_SHIPPED => 'Shipped',
        self::STATUS_COMPLETED => 'Completed',
    ];

    public const DELIVERY_COLLECT = 'collect';

    public const DELIVERY_COURIER = 'courier';

    public const DELIVERY_METHODS = [
        self::DELIVERY_COLLECT => 'Collect from Ranyati',
        self::DELIVERY_COURIER => 'Courier (waybill)',
    ];

    /** Statuses in which the member may still edit answers and documents. */
    public const MEMBER_EDITABLE = [self::STATUS_DRAFT, self::STATUS_CHANGES_REQUESTED];

    /** Statuses in which an admin works on the request before it is final. */
    public const IN_REVIEW = [self::STATUS_SUBMITTED, self::STATUS_VERIFIED, self::STATUS_AWAITING_ENDORSEMENT];

    /** Statuses that are part of fulfilment after the book has been printed. */
    public const FULFILMENT = [
        self::STATUS_INVOICED,
        self::STATUS_PAID,
        self::STATUS_READY_FOR_COLLECTION,
        self::STATUS_SHIPPED,
        self::STATUS_COMPLETED,
    ];

    protected $fillable = [
        'user_id',
        'application_type_id',
        'template_version_id',
        'status',
        'answers',
        'profile_snapshot',
        'blocks_snapshot',
        'pdf_disk',
        'pdf_path',
        'pdf_generated_at',
        'submitted_at',
        'verified_by',
        'verified_at',
        'approved_by',
        'approved_at',
        'printed_at',
        'reviewer_notes',
        'delivery_method',
        'courier',
        'waybill_number',
        'invoice_total',
        'payment_method',
        'payment_reference',
        'invoiced_at',
        'paid_at',
        'shipped_at',
        'ready_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'profile_snapshot' => 'array',
            'blocks_snapshot' => 'array',
            'pdf_generated_at' => 'datetime',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'printed_at' => 'datetime',
            'invoiced_at' => 'datetime',
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'ready_at' => 'datetime',
            'delivered_at' => 'datetime',
            'invoice_total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applicationType(): BelongsTo
    {
        return $this->belongsTo(ApplicationType::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(MotivationDocument::class)->orderBy('kind')->orderBy('id');
    }

    public function letters(): HasMany
    {
        return $this->hasMany(MotivationLetter::class)->orderByRaw('firearm_index IS NULL')->orderBy('firearm_index')->orderBy('id');
    }

    public function templateVersion(): BelongsTo
    {
        return $this->belongsTo(MotivationTemplateVersion::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    /** Status label shown to the member (admin review steps collapse to "Under review"). */
    public function memberStatusLabel(): string
    {
        return self::MEMBER_STATUSES[$this->status] ?? $this->statusLabel();
    }

    public function deliveryMethodLabel(): ?string
    {
        return $this->delivery_method ? (self::DELIVERY_METHODS[$this->delivery_method] ?? $this->delivery_method) : null;
    }

    public function invoice(): ?MotivationDocument
    {
        return $this->documents->firstWhere('kind', DocumentKinds::INVOICE);
    }

    public function proofOfPayment(): ?MotivationDocument
    {
        return $this->documents->firstWhere('kind', DocumentKinds::PROOF_OF_PAYMENT);
    }

    public function isInFulfilment(): bool
    {
        return in_array($this->status, self::FULFILMENT, true);
    }

    public function reference(): string
    {
        return 'RM-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function isMemberEditable(): bool
    {
        return in_array($this->status, self::MEMBER_EDITABLE, true);
    }

    public function isInReview(): bool
    {
        return in_array($this->status, self::IN_REVIEW, true);
    }

    public function hasBook(): bool
    {
        return (bool) $this->pdf_path;
    }

    /**
     * Profile data used for conditions and placeholders: the snapshot once an
     * admin has confirmed the details, otherwise the member's live profile.
     *
     * @return array<string, mixed>
     */
    public function profileData(): array
    {
        if ($this->profile_snapshot) {
            return ClientProfile::withDerived($this->profile_snapshot);
        }

        $profile = $this->user?->clientProfile;

        return $profile ? $profile->toPlaceholderArray() : ClientProfile::withDerived([]);
    }

    /**
     * Data context shared by every condition check on this request.
     *
     * @return array<string, mixed>
     */
    public function conditionContext(): array
    {
        return QuestionSchema::context($this->profileData(), $this->answers ?? [], $this->applicationType);
    }

    /**
     * Requirements whose condition currently passes, keyed by kind.
     *
     * @return array<string, array<string, mixed>>
     */
    public function applicableRequirements(): array
    {
        $context = $this->conditionContext();
        $out = [];
        foreach ($this->applicationType->requirements() as $req) {
            if (ConditionEvaluator::passes($req['condition'] ?? null, $context)) {
                $out[$req['kind']] = $req;
            }
        }

        return $out;
    }

    /**
     * Labels of required documents that have not been uploaded in sufficient
     * number. The endorsement is excluded — Ranyati requests it after the
     * motivation has been written.
     *
     * @return array<int, string>
     */
    public function missingDocuments(): array
    {
        $counts = $this->documents->countBy('kind');
        $missing = [];

        foreach ($this->applicableRequirements() as $kind => $req) {
            if (! ($req['required'] ?? false) || $kind === DocumentKinds::ENDORSEMENT) {
                continue;
            }
            $min = max(1, (int) ($req['min_count'] ?? 1));
            if (($counts[$kind] ?? 0) < $min) {
                $label = $req['label'] ?? DocumentKinds::label($kind);
                $missing[] = $min > 1 ? "{$label} ({$min} required)" : $label;
            }
        }

        return $missing;
    }

    /**
     * Labels of required questions still unanswered.
     *
     * @return array<int, string>
     */
    public function missingAnswers(): array
    {
        return QuestionSchema::missingRequired(
            $this->applicationType->sections(),
            $this->answers ?? [],
            $this->conditionContext(),
        );
    }

    public function needsEndorsement(): bool
    {
        $req = $this->applicableRequirements()[DocumentKinds::ENDORSEMENT] ?? null;

        return $req !== null
            && ($req['required'] ?? false)
            && ! $this->documents->contains('kind', DocumentKinds::ENDORSEMENT);
    }
}
