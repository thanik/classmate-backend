<?php

use Illuminate\Database\Seeder;
use App\Organization;

class SampleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sample_org = new Organization();
        $sample_org->name = "King Mongkut's University of Technology Thonburi";
        $sample_org->abbreviation = "KMUTT";
        $sample_org->country = "TH";
        $sample_org->save();
        $sample_org = new Organization();
        $sample_org->name = "King Mongkut's Institute of Technology Ladkrabang";
        $sample_org->abbreviation = "KMITL";
        $sample_org->country = "TH";
        $sample_org->save();
        $sample_org = new Organization();
        $sample_org->name = "King Mongkut's University of Technology North Bangkok";
        $sample_org->abbreviation = "KMUTNB";
        $sample_org->country = "TH";
        $sample_org->save();
        $sample_org = new Organization();
        $sample_org->name = "Kasetsart University";
        $sample_org->abbreviation = "KU";
        $sample_org->country = "TH";
        $sample_org->save();
        $sample_org = new Organization();
        $sample_org->name = "Chulalongkorn University";
        $sample_org->abbreviation = "CU";
        $sample_org->country = "TH";
        $sample_org->save();
        $sample_org = new Organization();
        $sample_org->name = "Mahidol University";
        $sample_org->abbreviation = "MU";
        $sample_org->country = "TH";
        $sample_org->save();
        $sample_org = new Organization();
        $sample_org->name = "Thammasat University";
        $sample_org->abbreviation = "TU";
        $sample_org->country = "TH";
        $sample_org->save();
    }
}
