<?php namespace CosmicRadioTV\Podcast\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use CosmicRadioTV\Podcast\Models\Episode;
use CosmicRadioTV\Podcast\Models\Show;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Component that lists all of the episodes of a show
 *
 * @package CosmicRadioTV\Podcast\Components
 */
class Episodes extends ComponentBase
{

    /**
     * @var Show The show being displayed
     */
    public $show;

    /**
     * @var LengthAwarePaginator|Episode[] Pagintor instance of all of the episodes
     */
    public $episodes;

    /**
     * Component Details
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'cosmicradiotv.podcast::components.episodes.name',
            'description' => 'cosmicradiotv.podcast::components.episodes.description',
        ];
    }

    /**
     * User editable properties
     *
     * @return array
     */
    public function defineProperties()
    {
        return [
            'showSlug'    => [
                'title'       => 'cosmicradiotv.podcast::components.episodes.properties.show_slug.title',
                'description' => 'cosmicradiotv.podcast::components.episodes.properties.show_slug.description',
                'default'     => '{{ :show_slug }}',
                'type'        => 'string',
            ],
            'perPage'     => [
                'title'             => 'cosmicradiotv.podcast::components.episodes.properties.per_page.title',
                'description'       => 'cosmicradiotv.podcast::components.episodes.properties.per_page.description',
                'default'           => 10,
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => trans('cosmicradiotv.podcast::components.episodes.properties.per_page.validationMessage'),
                'required'          => true,
            ],
            'episodePage' => [
                'title'       => 'cosmicradiotv.podcast::components.episodes.properties.episode_page.title',
                'description' => 'cosmicradiotv.podcast::components.episodes.properties.episode_page.description',
                'type'        => 'dropdown',
                'default'     => 'podcast/episode',
                'required'    => true,
                'group'       => trans('cosmicradiotv.podcast::components.episodes.properties.episode_page.group'),
            ]
        ];
    }

    /**
     * Prepare component
     *
     * @returns null|string
     */
    public function onRun()
    {
        try {
            $this->setState();
        } catch (ModelNotFoundException $e) {
            // Show not found, return 404
            $this->controller->setStatusCode(404);

            return $this->controller->run('404');
        }


        return null;
    }

    /**
     * Set components state based on parameters
     *
     * @throws ModelNotFoundException
     */
    public function setState()
    {
        $this->show = Show::query()->where('slug', $this->property('showSlug'))->firstOrFail();
        $this->episodes = $this->show->episodes()
                                     ->with('image')
                                     ->where('published', true)
                                     ->orderBy('release', 'desc')
                                     ->paginate(intval($this->property('perPage')));
    }

    /**
     * Gives values for Episode Page dropdown
     *
     * @return array
     */
    public function getEpisodePageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * Get episode's URL
     *
     * @param Episode $episode
     *
     * @return string
     */
    public function getEpisodeURL(Episode $episode)
    {
        return $this->controller->pageUrl($this->property('episodePage'),
            ['show_slug' => $this->show->slug, 'episode_slug' => $episode->slug]);
    }
}