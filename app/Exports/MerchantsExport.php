<?php


namespace App\Exports;

use App\Models\Merchant;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MerchantsExport implements FromCollection, WithHeadings, WithMapping
{
    private $rowNumber = 0;
    public function collection()
    {
        return Merchant::with(['details.criteria', 'domestic'])->get();
    }

    public function headings(): array
    {
        return [
            'No.',
            'Nama Merchant (max 50)',
            'Nama Merchant (max 25)',
            'MPAN',
            'MID',
            'Kota',
            'Kodepos',
            'Kriteria',
            'MCC',
            'Jml Terminal',
            'Tipe Merchant',
            'NPWP**',
            'KTP**',
            'Tipe QR',
            'NMID',
            'Tanggal Create',
            'No Rekening',
            'Kategori',
            'Merchant Domestik'
        ];
    }

    public function map($merchant): array
    {

        // dd($merchant->details->criteria->DESC);

        $this->rowNumber++;
        $desc = isset($merchant->details->criteria) ? $merchant->details->criteria->DESC : ''; 

        return [
            $this->rowNumber,
            $merchant->MERCHANT_NAME, 
            substr($merchant->MERCHANT_NAME, 0, 25), 
            $merchant->details ? $merchant->details->MPAN : '', 
            $merchant->details ? $merchant->details->MID : '', 
            $merchant->MERCHANT_CITY, 
            $merchant->POSTAL_CODE, 
            $merchant->details ? $merchant->details->CRITERIA : '', 
            $merchant->domestic ? $merchant->domestic->MCC : '', 
            '1',
            $merchant->MERCHANT_TYPE_2, 
            $merchant->NPWP, 
            $merchant->KTP, 
            $merchant->QR_TYPE, 
            $merchant->domestic ? $merchant->domestic->NMID : '', 
            $merchant->CREATED_AT, 
            $merchant->ACCOUNT_NUMBER,
            $desc,
            $merchant->MERCHANT_ADDRESS,
        ];
    }
    
}
