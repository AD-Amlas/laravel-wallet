<?php

declare(strict_types=1);

use Bavix\Wallet\Internal\Service\UuidServiceInterface;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Models\WalletInterface;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class UpdateWalletsUuidTable extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn($this->table(), 'uuid')) {
            return;
        }

        // upgrade from 6.x
        Schema::table($this->table(), function (Blueprint $table) {
            $table->uuid('uuid')
                ->after('slug')
                ->nullable()
                ->unique()
            ;
        });

        Wallet::query()->chunk(10000, static function (Collection $wallets) {
            $wallets->each(function (WalletInterface $wallet) {
                $wallet->uuid = app(UuidServiceInterface::class)->uuid4();
                $wallet->save();
            });
        });

        Schema::table($this->table(), static function (Blueprint $table) {
            $table->uuid('uuid')->change();
        });
    }

    public function down(): void
    {
        Schema::dropColumns($this->table(), ['uuid']);
    }

    protected function table(): string
    {
        return app(WalletInterface::class)->getTable();
    }
}
