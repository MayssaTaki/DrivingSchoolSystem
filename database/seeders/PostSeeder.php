<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Post;
use App\Models\PostFile;
use App\Models\User;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;

class PostSeeder extends Seeder
{
    public function run(): void
    {
         
        $user = User::where('role', 'employee')->first();


          $post1 = Post::create([
            'user_id' => $user->id,
            'title' => 'صورة ترحيبية',
            'body' => 'شكراً لاستخدامكم نظامنا.'
        ]);

        $imagePath = Storage::disk('public')->putFileAs(
            'post_files',
            new File(database_path('seeders/files/image.png')), // تأكد من وجود الصورة
            Str::uuid() . '.jpg'
        );

        PostFile::create([
            'post_id' => $post1->id,
            'path' => $imagePath,
            'original_name' => 'image.jpg',
            'type' => 'image'
        ]);

        $post2 = Post::create([
            'user_id' => $user->id,
            'title' => 'دليل السائق',
            'body' => 'ملف يحتوي على تعليمات القيادة.'
        ]);

        $pdfPath = Storage::disk('public')->putFileAs(
            'post_files',
            new File(database_path('seeders/files/driver_guide.pdf')),
            Str::uuid() . '.pdf'
        );

        PostFile::create([
            'post_id' => $post2->id,
            'path' => $pdfPath,
            'original_name' => 'دليل السائق.pdf',
            'type' => 'pdf'
        ]);

      
    }
}
