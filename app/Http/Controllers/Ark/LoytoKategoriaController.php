<?php

namespace App\Http\Controllers\Ark;

use App\Ark\LoytoKategoria;
use App\Http\Controllers\Controller;
use App\Library\String\MipJson;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Response;
use Exception;

class LoytoKategoriaController extends Controller {
	public function index() {
		try {
		
			$entities = LoytoKategoria::orderBy("nimi_fi", "ASC")->get();
			$total_rows = count($entities);
			MipJson::initGeoJsonFeatureCollection($total_rows, $total_rows);
		
			foreach ($entities as $entity) {
				MipJson::addGeoJsonFeatureCollectionFeaturePoint(null, $entity);
			}
		
		} catch(Exception $e) {
			MipJson::setResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
			MipJson::addMessage(Lang::get('loytoKategoria.get_failed'));
		}
			
		return MipJson::getJson();
	}
	
	public function store(Request $request) {
		try {
			// TODO access control test
				
			$entity = new LoytoKategoria($request->all());
			$entity->luoja = Auth::user()->id;
			$entity->save();
	
			MipJson::addMessage(Lang::get('loytoKategoria.save_success'));
			MipJson::setGeoJsonFeature(null, array("id" => $entity->id));
			MipJson::setResponseStatus(Response::HTTP_OK);
		} catch(AuthorizationException $e) {
			// just throw it on..
			throw $e;
		} catch(Exception $e) {
			MipJson::setGeoJsonFeature();
			MipJson::setResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
			MipJson::addMessage(Lang::get('loytoKategoria.save_failed'));
		}
	
		return MipJson::getJson();
	}
	
	public function update(Request $request, $id) {
		try {
			$entity = LoytoKategoria::find($id);
	
			if(!$entity){
				//error, entity not found
				MipJson::setGeoJsonFeature();
				MipJson::addMessage(Lang::get('loytoKategoria.search_not_found'));
				MipJson::setResponseStatus(Response::HTTP_NOT_FOUND);
				return MipJson::getJson();
			}
	
			// TODO access control test
			// $this->authorize('update', $entity);
	
			$entity->fill($request->all());
			$entity->muokkaaja = Auth::user()->id;
			$entity->muokattu = new \DateTime();
			$entity->save();
	
			MipJson::addMessage(Lang::get('loytoKategoria.save_success'));
			MipJson::setGeoJsonFeature(null, array("id" => $entity->id));
			MipJson::setResponseStatus(Response::HTTP_OK);
	
		} catch(AuthorizationException $e) {
			// just throw it on..
			throw $e;
		} catch(Exception $e) {
			MipJson::setGeoJsonFeature();
			MipJson::setResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
			MipJson::addMessage(Lang::get('loytoKategoria.save_failed'));
		}
	
		return MipJson::getJson();
	}
	
	public function destroy($id) {
		// TODO: access check!
	
		$entity = LoytoKategoria::find($id);
	
		if(!$entity) {
			// return: not found
			MipJson::setGeoJsonFeature();
			MipJson::addMessage(Lang::get('loytoKategoria.search_not_found'));
			MipJson::setResponseStatus(Response::HTTP_NOT_FOUND);
		}
		else  {
			$deleted_rows = $entity->delete();
			if($deleted_rows > 0) {
				MipJson::addMessage(Lang::get('loytoKategoria.delete_success'));
				MipJson::setGeoJsonFeature(null, array("id" => $entity->id));
			}
			else {
				MipJson::setGeoJsonFeature();
				MipJson::addMessage(Lang::get('loytoKategoria.delete_failed'));
				MipJson::setResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
			}
		}
		return MipJson::getJson();
	}
}
