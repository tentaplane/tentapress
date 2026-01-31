<?php

declare(strict_types=1);

namespace TentaPress\Seo\Models;

use Illuminate\Database\Eloquent\Model;

final class TpSeoPost extends Model
{
    protected $table = 'tp_seo_posts';

    protected $fillable = [
        'post_id',
        'title',
        'description',
        'canonical_url',
        'robots',
        'og_title',
        'og_description',
        'og_image',
        'twitter_title',
        'twitter_description',
        'twitter_image',
    ];
}
