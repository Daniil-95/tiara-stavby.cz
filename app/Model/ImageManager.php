<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Http\FileUpload;
use Nette\Utils\Random;

final class ImageManager
{
	private string $root;

	public function __construct()
	{
		$this->root = dirname(__DIR__, 2) . '/www/uploads';
	}

	public function saveProjectImage(FileUpload $upload): string
	{
		if (!$upload->isOk() || $upload->getSize() > 8 * 1024 * 1024) throw new \RuntimeException('Soubor není platný nebo překračuje 8 MB.');
		$info = @getimagesize($upload->getTemporaryFile());
		$mime = $upload->getContentType();
		$extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
		if (!$info || !isset($extensions[$mime]) || $info['mime'] !== $mime) throw new \RuntimeException('Povolené jsou pouze obrázky JPG, PNG, GIF a WebP.');
		$name = Random::generate(20) . '.' . $extensions[$mime];
		$directory = $this->root . '/projects';
		if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) throw new \RuntimeException('Upload directory is not writable.');
		$target = $directory . '/' . $name;
		$upload->move($target);
		$this->createDerivatives($target, $mime, (int) $info[0], (int) $info[1]);
		return '/uploads/projects/' . $name;
	}

	private function createDerivatives(string $path, string $mime, int $width, int $height): void
	{
		if (!function_exists('imagecreatefromstring')) return;
		$data = @file_get_contents($path);
		$source = $data === false ? false : @imagecreatefromstring($data);
		if (!$source) return;
		foreach ([480, 1200] as $targetWidth) {
			if ($width <= $targetWidth) continue;
			$targetHeight = (int) round($height * $targetWidth / $width);
			$copy = imagecreatetruecolor($targetWidth, $targetHeight);
			if ($mime === 'image/png' || $mime === 'image/webp' || $mime === 'image/gif') {
				imagealphablending($copy, false);
				imagesavealpha($copy, true);
				$transparent = imagecolorallocatealpha($copy, 0, 0, 0, 127);
				imagefilledrectangle($copy, 0, 0, $targetWidth, $targetHeight, $transparent);
			}
			imagecopyresampled($copy, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
			$derivative = preg_replace('/\.(jpg|png|gif|webp)$/i', '-' . $targetWidth . '.$1', $path);
			switch ($mime) {
				case 'image/jpeg': imagejpeg($copy, $derivative, 84); break;
				case 'image/png': imagepng($copy, $derivative, 7); break;
				case 'image/gif': imagegif($copy, $derivative); break;
				case 'image/webp': if (function_exists('imagewebp')) imagewebp($copy, $derivative, 84); break;
			}
			imagedestroy($copy);
		}
		imagedestroy($source);
	}
}
