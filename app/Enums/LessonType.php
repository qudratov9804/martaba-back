<?php

namespace App\Enums;

enum LessonType: string
{
    case Video = 'video';
    case Audio = 'audio';
    case Text = 'text';
    case Pdf = 'pdf';
    case Document = 'document';
    case Presentation = 'presentation';
    case Image = 'image';
    case Embed = 'embed';
    case Quiz = 'quiz';
    case Assignment = 'assignment';
    case Live = 'live';
    case ExternalLink = 'external_link';
    case Download = 'download';
    case Code = 'code';
    case Html = 'html';
}
