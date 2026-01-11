<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Billing\Models\BillingAuthorization;
use Modules\Billing\Models\PriceOverride;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Retainer;
use Modules\Billing\Models\CreditNote;
use Modules\Billing\Models\BillableEntry;
use App\Models\Customer;
use App\Models\User;
use Modules\Billing\Services\HelcimService;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property string|null $country
 * @property string|null $vat_number
 * @property array $settings
 * @property float $margin_floor_percent
 * @property bool $is_active
 * @property int|null $primary_contact_id
 * @property string|null $pricing_tier
 * @property string $billing_mode
 * @property float $account_balance
 * @property string|null $scenario
 * @property string|null $helcim_id
 * @property string|null $helcim_card_token
 * @property bool $sms_notifications_enabled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \Illuminate\Database\Eloquent\Collection|BillingAuthorization[] $billingAuthorizations
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $users
 * @property-read User|null $primaryContact
 * @property-read \Illuminate\Database\Eloquent\Collection|PriceOverride[] $priceOverrides
 * @property-read \Illuminate\Database\Eloquent\Collection|Subscription[] $subscriptions
 * @property-read \Illuminate\Database\Eloquent\Collection|Invoice[] $invoices
 * @property-read \Illuminate\Database\Eloquent\Collection|Retainer[] $retainers
 * @property-read \Illuminate\Database\Eloquent\Collection|CreditNote[] $creditNotes
 * @property-read \Illuminate\Database\Eloquent\Collection|BillableEntry[] $billableEntries
 * @property-read Customer|null $customer
 * @property-read \Modules\Crm\Models\Client|null $client
 */
class Company extends Model
{
    use HasFactory;

    public function client()
    {
        return $this->belongsTo(\Modules\Crm\Models\Client::class);
    }

    protected static function newFactory()
    {
        return \Modules\Billing\Database\Factories\CompanyFactory::new();
    }

        protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'country',
                'website',
        'notes',
                'is_active',
        'billing_address',
        'billing_mode',
        'pricing_tier',
        'scenario',
        'margin_floor_percent',
        'sms_notifications_enabled',
        'primary_contact_id',
        'client_id',
        'customer_id',
        'tax_id',
    ];

    protected $casts = [
        'settings' => 'array',
        'margin_floor_percent' => 'decimal:2',
        'is_active' => 'boolean',
        'account_balance' => 'decimal:2',
    ];

    /**
     * Create a Venn customer for the company.
     *
     * @param  array  $options
     * @return \Modules\Billing\Models\Company
     */
    public function createAsVennCustomer(array $options = [])
    {
        if ($this->venn_id) {
            return $this;
        }

        $vennService = app(VennService::class);
        $vennId = $vennService->createCustomer($this);

        if ($vennId) {
            $this->venn_id = $vennId;
            $this->save();
        }

        return $this;
    }

    public function billingAuthorizations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BillingAuthorization::class);
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user')
                    ->withPivot('role_id', 'status')
                    ->withTimestamps();
    }

    public function domains(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CompanyDomain::class);
    }

    public function primaryContact(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_contact_id');
    }

    public function customers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function quotes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function priceOverrides(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PriceOverride::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Subscription>
     */
    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function billableEntries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BillableEntry::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\Billing\Models\Payment::class);
    }

    public function retainers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Retainer::class);
    }

    public function creditNotes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    /**
     * Get the supported payment method types.
     *
     * @return array
     */
    public function supportedPaymentMethods()
    {
        return ['card', 'us_bank_account'];
    }

    public function getEffectivePrice(\Modules\Inventory\Models\Product $product, ?\Illuminate\Support\Carbon $date = null): float
    {
        // This logic will be handled by PricingEngineService, but we can add a convenience method here
        // or delegate to the service. For now, I'll leave it as a placeholder or simple implementation
        // if the service isn't injected.
        // Ideally, models shouldn't depend on services.
        // So this method might just check overrides and tiers directly if simple, 
        // but the requirement says "Check Override -> Tier -> Base".
        
        // Let's implement the basic logic here as requested by the Work Packet "Key Methods" section for Company Model.
        
        $date = $date ?? now();

        // 1. Check Override
        $override = $this->priceOverrides()
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->where(function ($query) use ($date) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $date);
            })
            ->where(function ($query) use ($date) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $date);
            })
            ->first();

        if ($override) {
            if ($override->type === 'fixed') {
                return $override->value;
            } elseif ($override->type === 'discount_percent') {
                return $product->base_price * (1 - ($override->value / 100));
            } elseif ($override->type === 'markup_percent') {
                return $product->base_price * (1 + ($override->value / 100));
            }
        }

        // 2. Check Tier
        // Assuming Product has a method getPriceForTier, or we query ProductTierPrice directly.
        // Since Product model is in another module, we should use the relationship if available or query.
        // The Product model enhancement is next in the todo list.
        // For now, let's assume we can access tier prices via product.
        
        $tierPrice = $product->tierPrices()
            ->where('tier', $this->pricing_tier)
            ->where(function ($query) use ($date) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $date);
            })
            ->where(function ($query) use ($date) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $date);
            })
            ->orderBy('starts_at', 'desc') // Get the most recent applicable price
            ->first();

        if ($tierPrice) {
            return $tierPrice->price;
        }

        // 3. Base Price
        return $product->base_price ?? 0.0;
    }

    public function hasOverrideFor(\Modules\Inventory\Models\Product $product): bool
    {
        return $this->priceOverrides()
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->exists();
    }

    public function isMarginSafe(float $price, float $cost): bool
    {
        if ($price <= 0) return false;
        $margin = (($price - $cost) / $price) * 100;
        return $margin >= $this->margin_floor_percent;
    }

    public function getPricingTierLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->pricing_tier));
    }

    /**
     * Create this company as a customer in Helcim.
     *
     * @return string|null
     */
    public function createAsHelcimCustomer(): ?string
    {
        if ($this->helcim_id) {
            return $this->helcim_id;
        }

        $helcimService = app(HelcimService::class);
        $customerCode = $helcimService->createCustomer($this);

        if ($customerCode) {
            $this->helcim_id = $customerCode;
            $this->save();
        }

        return $customerCode;
    }

    /**
     * Charge the company using their stored Helcim card token.
     *
     * @param float $amount
     * @param string $description
     * @return array|null
     */
    public function charge(float $amount, string $description = ''): ?array
    {
        if (!$this->helcim_id || !$this->helcim_card_token) {
            return null;
        }

        $helcimService = app(HelcimService::class);
        // Assuming we can pass description or order ID, but for now just amount
        // We might need to pass IP address from request() helper if available
        $ipAddress = request()->ip() ?? '127.0.0.1';
        
        return $helcimService->purchase($amount, $ipAddress, $this->helcim_id, $this->helcim_card_token);
    }
}
