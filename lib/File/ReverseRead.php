<?php

namespace SimpleSAML\Module\logpeek\File;

/**
 * Functionatility for line by line reverse reading of a file. It is done by blockwise
 * fetching the file from the end and putting the lines into an array.
 * 
 * @author Thomas Graff<thomas.graff@uninett.no>
 *
 */
class ReverseRead
{
    /**
     * 8192 is max number of octets limited by fread.
     * @var int
     */
    private $blockSize;

    /** @var int */
    private $blockStart;

    /** @var resource */
    private $fileHandle;

    /**
     * fileSize may be changed after initial file size check
     * @var int
     */
    private $fileSize;

    /** @var int */
    private $fileMtime;

    /** @var array Array containing file lines */
    private $content;

    /** @var string Leftover before first complete line */
    private $remainder;

    /** @var int  Count read lines from the end */
    private $readPointer;

	
    /**
     * File is checked and file handle to file is opend. But no data is read
     * from the file.
     * 
     * @param string $fileUrl Path and filename to file to be read
     * @param int $blockSize File read block size in byte
     * @return bool Success
     */
    public function __construct($fileUrl, $blockSize = 8192)
    {
        if (!is_readable($fileUrl)) {
            return false;
        }

        $this->blockSize = $blockSize;
        $this->content = [];
        $this->remainder = '';
        $this->readPointer = 0;
		
        $fileInfo = stat($fileUrl);
        $this->fileSize = $this->blockStart = $fileInfo['size'];
        $this->fileMtime = $fileInfo['mtime'];

        $this->fileHandle = fopen($fileUrl, 'rb');
    }


    public function __destruct()
    {
        fclose($this->fileHandle);
    }


    /**
     * Fetch chunk of data from file.
     * Each time this function is called, will it fetch a chunk
     * of data from the file. It starts from the end of the file
     * and work towards the beginning of the file.
     * 
     * @return string|false buffer with datablock.
     * Will return bool FALSE when there is no more data to get.
     */
    private function readChunk()
    {
        $splits = $this->blockSize;

        $this->blockStart -= $splits;
        if ($this->blockStart < 0) {
            $splits += $this->blockStart;
            $this->blockStart = 0;
        }

        // Return false if nothing more to read
        if ($splits === 0) {
            return false;
        }

        fseek($this->fileHandle, $this->blockStart, SEEK_SET);
        $buff = fread($this->fileHandle, $splits);

        return $buff;
    }

    /**
     * Get one line of data from the file, starting from the end of the file.
     * 
     * @return string|false One line of data from the file.
     * Bool FALSE when there is no more data to get.
     */
    public function getPreviousLine()
    {
        if (count($this->content) === 0 || $this->readPointer < 1) {
            do {
                $buff = $this->readChunk();
                if ($buff === false) {
                    // Empty buffer, no more to read.
                    if (strlen($this->remainder) > 0) {
                        $buff = $this->remainder;
                        $this->remainder = '';
                        // Exit from while-loop
                        break;
                    }

                    // Remainder also empty.
                    return false;
                }
                $eolPos = strpos($buff, "\n");

                if ($eolPos === false) {
                    // No eol found. Make buffer head of remainder and empty buffer.
                    $this->remainder = $buff . $this->remainder;
                    $buff = '';
                } elseif ($eolPos !== 0) {
                    // eol found.
                    $buff .= $this->remainder;
                    $this->remainder = substr($buff, 0, $eolPos);
                    $buff = substr($buff, $eolPos+1);
                } else {
                    // eol must be 0.
                    $buff .= $this->remainder;
                    $buff = substr($buff, 1);
                    $this->remainder = '';
                }
            } while(($buff !== false) && ($eolPos === false));

            $this->content = explode("\n", $buff);
            $this->readPointer = count($this->content);
        }

        if (count($this->content) > 0) {
            return $this->content[--$this->readPointer];
        } else {
            return false;
        }
    }


    /**
     * @param string &$haystack
     * @param string $needle
     * @param int $exit
     * @return string|false
     */
    private function cutHead(&$haystack, $needle, $exit)
    {
        /** @psalm-var int|false $pos */
        $pos = 0;
        $cnt = 0;
        // Holder på inntill antall ønskede linjer eller vi ikke finner flere linjer
        while ($cnt < $exit && ($pos = strpos($haystack, $needle, $pos)) !== false) {
            $pos++;
            $cnt++;
        }
        return ($pos === false) ? false : substr($haystack, $pos, strlen($haystack));
    }


    /**
     * FIXME: This function has some error, do not use before auditing and testing
     * @param int $lines
     * @return array
     */
    public function getTail($lines = 10)
    {
        $this->blockStart = $this->fileSize;
        $buff1 = [];
        $lastLines = [];

        while ($this->blockStart) {
            $buff = $this->readChunk();
            if ($buff === false) {
                break;
            }

            $lines -= substr_count($buff, "\n");

            if ($lines <= 0) {
                $buff1[] = $this->cutHead($buff, "\n", abs($lines)+1);
                break;
            }

            if ($buff === false) {
                break;
            }
            $buff1[] = $buff;
        }

        for ($i = count($buff1); $i >= 0; $i--) {
            $lastLines = array_merge($lastLines, explode("\n", $buff1[$i]));
        }

        return $lastLines;
    }


    /**
     * @param int $pos
     * @return string|false
     */
    private function getLineAtPost($pos)
    {
        if ($pos < 0 || $pos > $this->fileSize) {
            return false;
        }

        $seeker = $pos;
        fseek($this->fileHandle, $seeker, SEEK_SET);
        while ($seeker > 0 && fgetc($this->fileHandle) !== "\n"){
            fseek($this->fileHandle, --$seeker, SEEK_SET);
        }

        return rtrim(fgets($this->fileHandle));
    }


    /**
     * @return string|false
     */
    public function getFirstLine()
    {
        return $this->getLineAtPost(0);
    }


    /**
     * @return string|false
     */
    public function getLastLine()
    {
        return $this->getLineAtPost($this->fileSize - 2);
    }


    /**
     * @return int
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }


    /**
     * @return int
     */
    public function getFileMtime()
    {
        return $this->fileMtime;
    }
}
