<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $title = 'Store settings';

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasRole('owner');
    }

    public function mount(): void
    {
        $this->form->fill(Setting::allValues());
    }

    public function form(Form $form): Form
    {
        $bdPhone = ['nullable', 'regex:'.config('bd.phone_regex')];

        return $form->statePath('data')->schema([
            Forms\Components\Section::make('Shipping (৳)')->schema([
                Forms\Components\TextInput::make('shipping_free_threshold')->label('Free delivery everywhere from')->integer()->minValue(0)->required(),
                Forms\Components\TextInput::make('shipping_khulna_free_threshold')->label('Free delivery in Khulna from')->integer()->minValue(0)->required(),
                Forms\Components\TextInput::make('shipping_khulna_rate')->label('Khulna rate')->integer()->minValue(0)->required(),
                Forms\Components\TextInput::make('shipping_dhaka_rate')->label('Dhaka rate')->integer()->minValue(0)->required(),
                Forms\Components\TextInput::make('shipping_other_rate')->label('Rest of Bangladesh rate')->integer()->minValue(0)->required(),
            ])->columns(3),
            Forms\Components\Section::make('Payments')->description('bKash / Nagad appear at checkout only when a number is set.')->schema([
                Forms\Components\TextInput::make('bkash_number')->label('bKash merchant number')->tel()->rules($bdPhone),
                Forms\Components\TextInput::make('nagad_number')->label('Nagad merchant number')->tel()->rules($bdPhone),
            ])->columns(2),
            Forms\Components\Section::make('Contact')->schema([
                Forms\Components\TextInput::make('store_phone')->label('Store phone')->tel()->maxLength(20),
                Forms\Components\TextInput::make('whatsapp_number')->label('WhatsApp number')->helperText('With country code, e.g. 8801XXXXXXXXX')->regex('/^\d{10,15}$/'),
                Forms\Components\TextInput::make('facebook_url')->label('Facebook page URL')->url()->maxLength(255),
            ])->columns(3),
            Forms\Components\Section::make('Meta (Facebook) ads')
                ->description('Product catalog feed for Commerce Manager: '.url('/feeds/meta.xml'))
                ->schema([
                    Forms\Components\TextInput::make('fb_pixel_id')->label('Meta Pixel ID')
                        ->helperText('Events Manager → your dataset/pixel → the number under its name.')
                        ->regex('/^\d{5,20}$/'),
                    Forms\Components\TextInput::make('fb_domain_verification')->label('Domain verification code')
                        ->helperText('Business settings → Brand safety → Domains → lavenin.com → "Meta-tag": paste only the content="…" value.')
                        ->regex('/^[a-z0-9]{10,64}$/'),
                ])->columns(2),
            Forms\Components\Section::make('Google (optional)')->schema([
                Forms\Components\TextInput::make('ga4_id')->label('GA4 Measurement ID')->placeholder('G-XXXXXXX')->regex('/^G-[A-Z0-9]{4,15}$/'),
            ]),
        ]);
    }

    public function save(): void
    {
        Setting::write($this->form->getState());
        Notification::make()->title('Settings saved')->success()->send();
    }
}
