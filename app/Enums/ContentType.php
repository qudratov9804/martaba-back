<?php

namespace App\Enums;

enum ContentType: string
{
    case Text = 'text';
    case Video = 'video';
    case Audio = 'audio';
    case Pdf = 'pdf';
    case Image = 'image';
    case Embed = 'embed';
    case Download = 'download';
    case ExternalLink = 'external_link';
}
