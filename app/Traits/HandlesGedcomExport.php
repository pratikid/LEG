<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Family;
use App\Models\Individual;
use App\Models\Media;
use App\Models\Note;
use App\Models\Repository;
use App\Models\Source;
use App\Models\Tree;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Trait for handling GEDCOM export operations
 */
trait HandlesGedcomExport
{
    /**
     * Generate a GEDCOM file for a tree.
     *
     * @param Tree $tree The tree to export
     * @param bool $includeMedia Whether to include media references
     * @param bool $includePrivateData Whether to include private data
     * @param string $disk Storage disk to use
     * @return string The path to the generated GEDCOM file
     */
    protected function generateGedcomFile(
        Tree $tree,
        bool $includeMedia = true,
        bool $includePrivateData = false,
        string $disk = 'local'
    ): string {
        // Generate a unique filename
        $filename = 'gedcom_' . $tree->id . '_' . Str::uuid() . '.ged';
        $directory = 'exports';
        $path = $directory . '/' . $filename;
        
        // Ensure the directory exists
        Storage::disk($disk)->makeDirectory($directory);
        
        // Start building the GEDCOM content
        $gedcom = $this->generateGedcomHeader($tree);
        
        // Add individuals
        $individuals = Individual::where('tree_id', $tree->id)->get();
        foreach ($individuals as $individual) {
            $gedcom .= $this->generateIndividualRecord($individual, $includeMedia, $includePrivateData);
        }
        
        // Add families
        $families = Family::where('tree_id', $tree->id)->get();
        foreach ($families as $family) {
            $gedcom .= $this->generateFamilyRecord($family, $includeMedia, $includePrivateData);
        }
        
        // Add sources
        $sources = Source::where('tree_id', $tree->id)->get();
        foreach ($sources as $source) {
            $gedcom .= $this->generateSourceRecord($source);
        }
        
        // Add repositories
        $repositories = Repository::where('tree_id', $tree->id)->get();
        foreach ($repositories as $repository) {
            $gedcom .= $this->generateRepositoryRecord($repository);
        }
        
        // Add notes
        $notes = Note::where('tree_id', $tree->id)->get();
        foreach ($notes as $note) {
            $gedcom .= $this->generateNoteRecord($note);
        }
        
        // Add media
        if ($includeMedia) {
            $media = Media::where('tree_id', $tree->id)->get();
            foreach ($media as $mediaItem) {
                $gedcom .= $this->generateMediaRecord($mediaItem);
            }
        }
        
        // Add trailer
        $gedcom .= "0 TRLR\n";
        
        // Write the GEDCOM content to the file
        Storage::disk($disk)->put($path, $gedcom);
        
        return $path;
    }
    
    /**
     * Generate the GEDCOM header.
     *
     * @param Tree $tree The tree being exported
     * @return string The GEDCOM header
     */
    protected function generateGedcomHeader(Tree $tree): string
    {
        $now = now();
        $date = $now->format('d M Y');
        $time = $now->format('H:i:s');
        
        $header = "0 HEAD\n";
        $header .= "1 SOUR LEGFAM\n";
        $header .= "2 NAME Legacy Family Tree\n";
        $header .= "2 VERS 1.0.0\n";
        $header .= "1 DATE $date\n";
        $header .= "2 TIME $time\n";
        $header .= "1 SUBM @SUBM@\n";
        $header .= "1 GEDC\n";
        $header .= "2 VERS 5.5.1\n";
        $header .= "2 FORM LINEAGE-LINKED\n";
        $header .= "1 CHAR UTF-8\n";
        
        // Add tree information
        $header .= "1 NOTE $tree->name\n";
        if (!empty($tree->description)) {
            $header .= "2 CONT $tree->description\n";
        }
        
        // Add submitter record
        $header .= "0 @SUBM@ SUBM\n";
        $header .= "1 NAME " . ($tree->user->name ?? 'Unknown') . "\n";
        
        return $header;
    }
    
    /**
     * Generate a GEDCOM individual record.
     *
     * @param Individual $individual The individual
     * @param bool $includeMedia Whether to include media references
     * @param bool $includePrivateData Whether to include private data
     * @return string The GEDCOM individual record
     */
    protected function generateIndividualRecord(
        Individual $individual,
        bool $includeMedia = true,
        bool $includePrivateData = false
    ): string {
        $gedcom = "0 @I{$individual->id}@ INDI\n";
        
        // Add name
        $gedcom .= "1 NAME {$individual->given_name} /{$individual->surname}/\n";
        if (!empty($individual->nickname)) {
            $gedcom .= "2 NICK {$individual->nickname}\n";
        }
        
        // Add sex
        $gedcom .= "1 SEX {$individual->sex}\n";
        
        // Add birth information
        if (!empty($individual->birth_date) || !empty($individual->birth_place)) {
            $gedcom .= "1 BIRT\n";
            if (!empty($individual->birth_date)) {
                $gedcom .= "2 DATE {$individual->birth_date}\n";
            }
            if (!empty($individual->birth_place)) {
                $gedcom .= "2 PLAC {$individual->birth_place}\n";
            }
        }
        
        // Add death information
        if (!empty($individual->death_date) || !empty($individual->death_place)) {
            $gedcom .= "1 DEAT\n";
            if (!empty($individual->death_date)) {
                $gedcom .= "2 DATE {$individual->death_date}\n";
            }
            if (!empty($individual->death_place)) {
                $gedcom .= "2 PLAC {$individual->death_place}\n";
            }
        }
        
        // Add occupation
        if (!empty($individual->occupation)) {
            $gedcom .= "1 OCCU {$individual->occupation}\n";
        }
        
        // Add note
        if (!empty($individual->note)) {
            $gedcom .= "1 NOTE {$individual->note}\n";
            
            // Split long notes into continuation lines
            $noteLines = str_split($individual->note, 60);
            for ($i = 1; $i < count($noteLines); $i++) {
                $gedcom .= "2 CONT {$noteLines[$i]}\n";
            }
        }
        
        // Add family links
        foreach ($individual->familiesAsChild as $family) {
            $gedcom .= "1 FAMC @F{$family->id}@\n";
        }
        
        foreach ($individual->familiesAsHusband as $family) {
            $gedcom .= "1 FAMS @F{$family->id}@\n";
        }
        
        foreach ($individual->familiesAsWife as $family) {
            $gedcom .= "1 FAMS @F{$family->id}@\n";
        }
        
        return $gedcom;
    }
    
    /**
     * Generate a GEDCOM family record.
     *
     * @param Family $family The family
     * @param bool $includeMedia Whether to include media references
     * @param bool $includePrivateData Whether to include private data
     * @return string The GEDCOM family record
     */
    protected function generateFamilyRecord(
        Family $family,
        bool $includeMedia = true,
        bool $includePrivateData = false
    ): string {
        $gedcom = "0 @F{$family->id}@ FAM\n";
        
        // Add husband
        if ($family->husband_id) {
            $gedcom .= "1 HUSB @I{$family->husband_id}@\n";
        }
        
        // Add wife
        if ($family->wife_id) {
            $gedcom .= "1 WIFE @I{$family->wife_id}@\n";
        }
        
        // Add children
        foreach ($family->children as $child) {
            $gedcom .= "1 CHIL @I{$child->id}@\n";
        }
        
        // Add marriage information
        if (!empty($family->marriage_date) || !empty($family->marriage_place) || !empty($family->marriage_type)) {
            $gedcom .= "1 MARR\n";
            if (!empty($family->marriage_date)) {
                $gedcom .= "2 DATE {$family->marriage_date}\n";
            }
            if (!empty($family->marriage_place)) {
                $gedcom .= "2 PLAC {$family->marriage_place}\n";
            }
            if (!empty($family->marriage_type)) {
                $gedcom .= "2 TYPE {$family->marriage_type}\n";
            }
        }
        
        // Add divorce information
        if (!empty($family->divorce_date) || !empty($family->divorce_place)) {
            $gedcom .= "1 DIV\n";
            if (!empty($family->divorce_date)) {
                $gedcom .= "2 DATE {$family->divorce_date}\n";
            }
            if (!empty($family->divorce_place)) {
                $gedcom .= "2 PLAC {$family->divorce_place}\n";
            }
        }
        
        return $gedcom;
    }
    
    /**
     * Generate a GEDCOM source record.
     *
     * @param Source $source The source
     * @return string The GEDCOM source record
     */
    protected function generateSourceRecord(Source $source): string
    {
        $gedcom = "0 @S{$source->id}@ SOUR\n";
        
        // Add title
        if (!empty($source->title)) {
            $gedcom .= "1 TITL {$source->title}\n";
        }
        
        // Add author
        if (!empty($source->author)) {
            $gedcom .= "1 AUTH {$source->author}\n";
        }
        
        // Add publication
        if (!empty($source->publication)) {
            $gedcom .= "1 PUBL {$source->publication}\n";
        }
        
        // Add repository reference
        if ($source->repository_id) {
            $gedcom .= "1 REPO @R{$source->repository_id}@\n";
            if (!empty($source->call_number)) {
                $gedcom .= "2 CALN {$source->call_number}\n";
            }
        }
        
        return $gedcom;
    }
    
    /**
     * Generate a GEDCOM repository record.
     *
     * @param Repository $repository The repository
     * @return string The GEDCOM repository record
     */
    protected function generateRepositoryRecord(Repository $repository): string
    {
        $gedcom = "0 @R{$repository->id}@ REPO\n";
        
        // Add name
        if (!empty($repository->name)) {
            $gedcom .= "1 NAME {$repository->name}\n";
        }
        
        // Add address
        if (!empty($repository->address)) {
            $gedcom .= "1 ADDR {$repository->address}\n";
            
            // Split address into continuation lines
            $addressLines = explode("\n", $repository->address);
            for ($i = 1; $i < count($addressLines); $i++) {
                $gedcom .= "2 CONT {$addressLines[$i]}\n";
            }
        }
        
        return $gedcom;
    }
    
    /**
     * Generate a GEDCOM note record.
     *
     * @param Note $note The note
     * @return string The GEDCOM note record
     */
    protected function generateNoteRecord(Note $note): string
    {
        $gedcom = "0 @N{$note->id}@ NOTE {$note->text}\n";
        
        // Split long notes into continuation lines
        $noteLines = str_split($note->text, 60);
        for ($i = 1; $i < count($noteLines); $i++) {
            $gedcom .= "1 CONT {$noteLines[$i]}\n";
        }
        
        return $gedcom;
    }
    
    /**
     * Generate a GEDCOM media record.
     *
     * @param Media $media The media item
     * @return string The GEDCOM media record
     */
    protected function generateMediaRecord(Media $media): string
    {
        $gedcom = "0 @M{$media->id}@ OBJE\n";
        
        // Add file reference
        if (!empty($media->file_path)) {
            $gedcom .= "1 FILE {$media->file_path}\n";
            $gedcom .= "2 FORM {$media->file_type}\n";
        }
        
        // Add title
        if (!empty($media->title)) {
            $gedcom .= "1 TITL {$media->title}\n";
        }
        
        // Add note
        if (!empty($media->note)) {
            $gedcom .= "1 NOTE {$media->note}\n";
            
            // Split long notes into continuation lines
            $noteLines = str_split($media->note, 60);
            for ($i = 1; $i < count($noteLines); $i++) {
                $gedcom .= "2 CONT {$noteLines[$i]}\n";
            }
        }
        
        return $gedcom;
    }
    
    /**
     * Format a date for GEDCOM export.
     *
     * @param string|null $date The date to format
     * @return string|null The formatted date
     */
    protected function formatDateForGedcom(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }
        
        // Try to parse the date
        try {
            $carbonDate = new \Carbon\Carbon($date);
            return $carbonDate->format('d M Y');
        } catch (\Exception $e) {
            // If parsing fails, return the original date
            return $date;
        }
    }
    
    /**
     * Clean text for GEDCOM export (remove invalid characters).
     *
     * @param string|null $text The text to clean
     * @return string|null The cleaned text
     */
    protected function cleanTextForGedcom(?string $text): ?string
    {
        if (empty($text)) {
            return null;
        }
        
        // Replace @ with @@
        $text = str_replace('@', '@@', $text);
        
        // Remove control characters
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        
        return $text;
    }
}