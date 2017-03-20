<?php

namespace App\Http\Controllers\Adm\Mship;

use Redirect;
use App\Http\Controllers\Adm\AdmController;
use App\Models\Mship\Note\Type as NoteType;
use App\Http\Requests\Mship\Note\Type\CreateEditNoteType;

class Note extends AdmController
{
    public function getTypeIndex()
    {
        $types = NoteType::orderBy('name', 'ASC')->get();

        return $this->viewMake('adm.mship.note.type.index')
                    ->with('types', $types);
    }

    public function getTypeCreate()
    {
        return $this->viewMake('adm.mship.note.type.create_or_update')
                    ->with('colourCodes', NoteType::getNoteColourCodes());
    }

    public function postTypeCreate(CreateEditNoteType $request)
    {
        $noteType = new NoteType($request->only(['name', 'short_code', 'colour_code', 'is_available', 'is_default']));
        if (!$noteType->save()) {
            return Redirect::route('adm.mship.note.type.create')->withErrors($noteType->errors());
        }

        return Redirect::route('adm.mship.note.type.index')->withSuccess("Type '".$noteType->name."' has been created!");
    }

    public function getTypeUpdate(NoteType $noteType)
    {
        if (!$noteType or !$noteType->exists) {
            return Redirect::route('adm.mship.note.type.index')->withError("Note type doesn't exist!");
        }

        return $this->viewMake('adm.mship.note.type.create_or_update')
                    ->with('noteType', $noteType)
                    ->with('colourCodes', NoteType::getNoteColourCodes());
    }

    public function postTypeUpdate(CreateEditNoteType $request, NoteType $noteType)
    {
        if (!$noteType or !$noteType->exists) {
            return Redirect::route('adm.mship.note.type.index')->withError("Note type doesn't exist!");
        }

        // Let's create!
        $noteType->fill($request->only(['name', 'short_code', 'colour_code', 'is_available', 'is_default']));
        if (!$noteType->save()) {
            return Redirect::route('adm.mship.note.type.update')->withErrors($noteType->errors());
        }

        return Redirect::route('adm.mship.note.type.index')->withSuccess("Note type '".$noteType->name."' has been updated!");
    }

    public function anyTypeDelete(NoteType $noteType)
    {
        if (!$noteType or !$noteType->exists) {
            return Redirect::route('adm.mship.note.type.index')->withError("Note type doesn't exist!");
        }

        // Is it the default role?
        if ($noteType->is_default) {
            return Redirect::route('adm.mship.note.type.index')->withError('You cannot delete the default note type.');
        }

        // Is it a system role?
        if ($noteType->is_system) {
            return Redirect::route('adm.mship.note.type.index')->withError('You cannot delete a system note type.');
        }

        // Let's delete!
        $noteType->delete();

        return Redirect::route('adm.mship.note.type.index')->withSuccess("Note Type '".$noteType->name."' has been deleted.  Any existing notes have not been impacted.");
    }
}
