<?php

namespace WursterMedien\Ui;

use Filament\Actions\Action;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Support\Enums\Size;

/**
 * 2FA per Authenticator-App wie bei Filament, aber „Einrichten“ als großer Button statt als Link.
 * Bei Pflicht ist er nach dem ersten Login das Einzige, was der Nutzer tun kann.
 */
class ProminentAppAuthentication extends AppAuthentication
{
    /**
     * @return array<Action>
     */
    public function getActions(): array
    {
        return array_map(
            fn (Action $action): Action => $action->getName() === 'setUpAppAuthentication'
                ? $action->button()->size(Size::Large)
                : $action,
            parent::getActions(),
        );
    }
}
