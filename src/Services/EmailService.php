<?php

namespace App\Services;

use App\Entity\Comment;
use App\Entity\Post;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class EmailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function notifyPostAuthorAboutNewComment(Post $post, Comment $comment)
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@blog-demo.com', 'Blog Demo'))
            ->to($post->getAuthor()->getEmail())
            ->subject('New comment on your post')
            ->htmlTemplate('emails/new-comment.html.twig')
            ->context([
                'post' => $post,
                'comment' => $comment,
            ]);

        $this->mailer->send($email);
    }
}