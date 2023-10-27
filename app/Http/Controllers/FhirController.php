<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use Satusehat\Integration\OAuth2Client;
use Satusehat\Integration\KYC;
use Satusehat\Integration\FHIR\Encounter;

class FhirController extends Controller
{
    // KYC Example
    public function kyc()
    {
        $kyc_iframe = true;
        return view('fhirdemo', compact('kyc_iframe'));
    }

    public function kyc_url()
    {
        // Check SATUSEHAT_ENV if not PROD, redirect to home with error message
        if(env('SATUSEHAT_ENV')!="PROD"){
            return redirect('home')->with('error', 'KYC memerlukan settingan environment SATUSEHAT Production.');
        }

        $kyc = new KYC;

        // Pass current user name and NIK to generate URL
        $json = $kyc->generateUrl(Auth::user()->name, Auth::user()->nik);
        $kyc_link = json_decode($json, true);

        return redirect($kyc_link['data']['url']);
    }

    // Token Test
    public function token()
    {
        $client = new OAuth2Client;
        $token = $client->token();

        return view('fhirdemo', compact('token'));
    }

    // Create Encounter Object Test
    public function encounter()
    {
        $encounter = new Encounter;
        $statusHistory = ['arrived' => '{timestamp_kedatangan}',
                    'inprogress' => '{timestamp_pemeriksaan}',
                    'finished' => '{timestamp_pulang}'];

        $encounter->addRegistrationId('123456789'); // unique string free text (increments / UUID)
        $encounter->addStatusHistory($statusHistory); // array of timestamp
        $encounter->setConsultationMethod('RAJAL'); // RAJAL, IGD, RANAP, HOMECARE, TELEKONSULTASI
        $encounter->setSubject('P12312312123', 'TESTER'); // ID SATUSEHAT Pasien dan Nama SATUSEHAT
        $encounter->addParticipant('102938712983', 'dr. X'); // ID SATUSEHAT Dokter, Nama Dokter
        $encounter->addLocation('A1-001', 'Ruang Poli A1'); // ID SATUSEHAT Location, Nama Poli
        $encounter->addDiagnosis(Str::uuid()->toString(), 'J06.9'); // ID SATUSEHAT Condition, Kode ICD10
        $encounter = $encounter->json();

        return view('fhirdemo', compact('encounter'));
    }
}
