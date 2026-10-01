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
		return $this->saveImage($upload, 'projects');
	}

	public function saveContentImage(FileUpload $upload): string
	{
		return $this->saveImage($upload, 'site');
	}

	private function saveImage(FileUpload $upload, string $collection): string
	{
		if (!$upload->isOk() || $upload->getSize() > 8 * 1024 * 1024) throw new \RuntimeException('Soubor není platný nebo překračuje 8 MB.');
		$info = @getimagesize($upload->getTemporaryFile());
		$mime = $upload->getContentType();
		$extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
		if (!$info || !isset($extensions[$mime]) || $info['mime'] !== $mime) throw new \RuntimeException('Povolené jsou pouze obrázky JPG, PNG, GIF a WebP.');
		if ((int) $info[0] > 14000 || (int) $info[1] > 14000 || (int) $info[0] * (int) $info[1] > 80_000_000) throw new \RuntimeException('Rozměry obrázku jsou příliš velké.');
		$name = Random::generate(20) . '.' . $extensions[$mime];
		$directory = $this->root . '/' . $collection;
		if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) throw new \RuntimeException('Upload directory is not writable.');
		$target = $directory . '/' . $name;
		$upload->move($target);
		$this->createDerivatives($target, $mime, (int) $info[0], (int) $info[1]);
		return '/uploads/' . $collection . '/' . $name;
	}

	public function safeUrl(?string $path, string $fallback = '/images/project-fallback.svg'): string
	{
		if (!$path) return $fallback;
		if (str_starts_with($path, '/uploads/projects/') || str_starts_with($path, '/uploads/site/')) {
			$collection = str_starts_with($path, '/uploads/site/') ? 'site' : 'projects';
			$name = basename($path);
			if (preg_match('/^[a-zA-Z0-9_-]+\.(?:jpg|png|gif|webp)$/i', $name) && is_file($this->root . '/' . $collection . '/' . $name)) return '/uploads/' . $collection . '/' . $name;
			return $fallback;
		}
		if (str_starts_with($path, '/images/')) {
			$name = basename($path);
			if ($name !== $path && preg_match('/^[a-zA-Z0-9_-]+\.(?:svg|jpg|png|webp)$/i', $name) && is_file(dirname($this->root) . '/images/' . $name)) return '/images/' . $name;
			return $fallback;
		}
		$parts = parse_url($path);
		if (($parts['scheme'] ?? null) === 'https' && ($parts['host'] ?? null) === 'images.unsplash.com' && empty($parts['user']) && empty($parts['pass'])) return $path;
		return $fallback;
	}

	public function srcSet(?string $path, string $fallback = '/images/project-fallback.svg'): string
	{
		$url = $this->safeUrl($path, $fallback);
		$parts = parse_url($url);
		if (($parts['host'] ?? null) === 'images.unsplash.com') {
			$query = [];
			parse_str($parts['query'] ?? '', $query);
			$sources = [];
			foreach ([480, 1200] as $width) {
				$query['w'] = $width;
				$sources[] = ($parts['scheme'] ?? 'https') . '://' . $parts['host'] . ($parts['path'] ?? '') . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986) . ' ' . $width . 'w';
			}
			return implode(', ', $sources);
		}
		if (str_starts_with($url, '/uploads/projects/') || str_starts_with($url, '/uploads/site/')) {
			$collection = str_starts_with($url, '/uploads/site/') ? 'site' : 'projects';
			$name = basename($url);
			$extension = pathinfo($name, PATHINFO_EXTENSION);
			$base = substr($name, 0, -strlen($extension) - 1);
			$sources = [];
			foreach ([480, 1200] as $width) {
				$variant = $base . '-' . $width . '.' . $extension;
				$src = is_file($this->root . '/' . $collection . '/' . $variant) ? '/uploads/' . $collection . '/' . $variant : $url;
				$sources[] = $src . ' ' . $width . 'w';
			}
			return implode(', ', $sources);
		}
		return $url . ' 480w, ' . $url . ' 1200w';
	}

	public function deleteProjectImage(?string $path): void
	{
		$this->deleteImage($path, 'projects');
	}

	public function deleteContentImage(?string $path): void
	{
		$this->deleteImage($path, 'site');
	}

	private function deleteImage(?string $path, string $collection): void
	{
		$prefix = '/uploads/' . $collection . '/';
		if (!$path || !str_starts_with($path, $prefix)) return;
		$name = basename($path);
		if (!preg_match('/^[a-zA-Z0-9_-]+\.(?:jpg|png|gif|webp)$/i', $name)) return;
		$extension = pathinfo($name, PATHINFO_EXTENSION);
		$base = substr($name, 0, -strlen($extension) - 1);
		foreach ([$base . '.' . $extension, $base . '-480.' . $extension, $base . '-1200.' . $extension] as $file) {
			$target = $this->root . '/' . $collection . '/' . $file;
			if (is_file($target)) @unlink($target);
		}
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
