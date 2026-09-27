<?php

namespace App\Enums;

enum TreatmentStatus: string
{
    case Treatment = 'treatment';
    case Rehabilitation = 'rehabilitation';
    case Vlk = 'vlk';
    case OnDuty = 'on_duty';
    case Leave = 'leave';
    case Awol = 'awol';
    case DiedInHospital = 'died_in_hospital';
    case DischargedMedically = 'discharged_medically';

    public function label(): string
    {
        return match ($this) {
            self::Treatment => 'Лікування',
            self::Rehabilitation => 'Реабілітація',
            self::Vlk => 'ВЛК',
            self::OnDuty => 'У строю',
            self::Leave => 'Відпустка',
            self::Awol => 'СЗЧ',
            self::DiedInHospital => 'Помер у лікарні',
            self::DischargedMedically => 'Комісований',
        };
    }
}
