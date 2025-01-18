<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use Satusehat\Integration\OAuth2Client;
use Satusehat\Integration\KYC;
use Satusehat\Integration\FHIR\Encounter;
use Satusehat\Integration\FHIR\Condition;
use Satusehat\Integration\FHIR\Organization;

use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

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
        $uuid = Uuid::uuid4()->toString();
        $encounter->addRegistrationId($uuid); // unique string free text (increments / UUID)

        $encounter->setArrived(Carbon::now()->subMinutes(15)->toDateTimeString());
        $encounter->setInProgress(Carbon::now()->subMinutes(5)->toDateTimeString(), Carbon::now()->toDateTimeString());
        // $encounter->setFinished(Carbon::now()->toDateTimeString());

        $encounter->addRegistrationId('1234567890'); // unique string free text (increments / UUID)
        $encounter->setConsultationMethod('RAJAL'); // RAJAL, IGD, RANAP, HOMECARE, TELEKONSULTASI
        $encounter->setSubject('P02478375538', 'Ardianto Putra'); // ID SATUSEHAT Pasien dan Nama SATUSEHAT
        $encounter->addParticipant('10009880728', 'dr. Alexander'); // ID SATUSEHAT Dokter, Nama Dokter
        $encounter->addLocation('b017aa54-f1df-4ec2-9d84-8823815d7228', 'Ruang Poli A1'); // ID SATUSEHAT Location, Nama Poli
        // $encounter->addDiagnosis(Str::uuid()->toString(), 'J06.9'); // ID SATUSEHAT Condition, Kode ICD10
        // $encounter = $encounter->json();

        // Contoh POST
        [$encounter, $res] = $encounter->post();
        $encounter = json_encode($res, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return view('fhirdemo', compact('encounter'));
    }

    // Create Condition Object Test
    public function condition()
    {
        // Condition
        $condition = new Condition;
        $condition->addClinicalStatus('active'); // active, inactive, resolved. Default bila tidak dideklarasi = active
        $condition->addCategory('diagnosis'); // Diagnosis, Keluhan. Default : Diagnosis
        $condition->addCode('J06.9'); // Kode ICD10
        $condition->setSubject('P12312312123', 'TESTER'); // ID SATUSEHAT Pasien dan Nama SATUSEHAT
        $condition->setEncounter(Str::uuid()->toString()); // ID SATUSEHAT Encounter
        $condition->setOnsetDateTime(Carbon::now()->toDateTimeString()); // timestamp onset. Timestamp sekarang
        $condition->setRecordedDate(Carbon::now()->toDateTimeString()); // timestamp recorded. Timestamp sekarang
        $condition = $condition->json();

        // // Uji Coba POST
        // [$statusCode, $res] = $condition->post();
        // $condition = json_encode($res, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return view('fhirdemo', compact('condition'));
    }

    // Create Organization
    public function organization()
    {
        // Condition
        $organization = new Organization;
        $uuid = Uuid::uuid4()->toString();
        $t = 'RS Sakit Cepat Sembuh';
        $organization->addIdentifier($uuid); // unique string free text (increments / UUID / inisial)
        $organization->setName($t); // string free text
        $organization = $organization->json();

        // // Uji Coba POST
        // [$statusCode, $res] = $organization->post();
        // $organization = json_encode($res, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);


        return view('fhirdemo', compact('organization'));
    }
}
