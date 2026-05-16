<?php

namespace Statica;

enum SettingEntryType: string
{
    case Single = 'single';
    case SingleTranslatable = 'single-translatable';

    case List = 'list';
    case ListTranslatable = 'list-translatable';
}
