<tr>
    <td class="header" style="padding: 42px 24px 30px 24px; text-align: center; background: linear-gradient(135deg, #3F2B1B 0%, #5C432C 45%, #8B5B2E 100%);">
        <a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
            @php
                $logoUrl = config('mail.logo_url');
            @endphp
            
            @if($logoUrl)
                <img src="{{ $logoUrl }}" 
                     class="logo" 
                     alt="{{ config('app.name') }}" 
                     style="max-height: 68px; width: auto; filter: brightness(1.05);">
            @else
                <div style="display: inline-flex; align-items: center; gap: 14px;">
                    <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #D4A017, #E07A5F); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 28px; box-shadow: 0 8px 24px rgba(212, 160, 23, 0.32);">
                        A
                    </div>
                    <div style="text-align: left;">
                        <span style="font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 700; color: #FAF6F0; letter-spacing: -1px;">
                            Alter Studio
                        </span>
                        <div style="font-size: 10px; color: #F3E2C5; letter-spacing: 2.4px; margin-top: 2px; font-weight: 600; text-transform: uppercase;">
                            Premium Moments
                        </div>
                    </div>
                </div>
            @endif
        </a>

        <div style="margin-top: 16px; font-size: 12px; line-height: 1.6; color: #F3E2C5;">
            Studio fotografi untuk pengalaman booking, produksi, dan hasil akhir yang lebih rapi.
        </div>
    </td>
</tr>

<tr>
    <td style="height: 1px; background: linear-gradient(to right, transparent, rgba(231, 217, 194, 0.2), #E7D9C2, rgba(231, 217, 194, 0.2), transparent);"></td>
</tr>
