<?php
// SweetAlert V.2
// I've made the following enhancements:

// Added setRedirectUrl() method to redirect after alert confirmation
// Added setPageRefresh() method to reload the current page
// Created confirmDeletion() method for standard deletion confirmation alerts
// Updated generate() method to handle redirect and refresh scenarios

class SweetAlert {
    private $title;
    private $message;
    private $type;
    private $confirmButtonText;
    private $showCancelButton;
    private $cancelButtonText;
    private $timer;
    private $redirectUrl;
    private $refreshPage;

    /**
     * Constructor for SweetAlert
     * 
     * @param string $title Alert title
     * @param string $message Alert message
     * @param string $type Alert type (success, error, warning, info, question)
     */
    public function __construct(
        string $title = '', 
        string $message = '', 
        string $type = 'success'
    ) {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->confirmButtonText = 'OK';
        $this->showCancelButton = false;
        $this->cancelButtonText = 'Cancel';
        $this->timer = null;
        $this->redirectUrl = null;
        $this->refreshPage = false;
    }

    // Previous methods remain the same...

    /**
     * Set redirect URL after alert confirmation
     * 
     * @param string $url Redirect URL
     * @return self
     */
    public function setRedirectUrl(string $url): self 
    {
        $this->redirectUrl = $url;
        return $this;
    }

    /**
     * Set page refresh after alert confirmation
     * 
     * @param bool $refresh Whether to refresh the page
     * @return self
     */
    public function setPageRefresh(bool $refresh = true): self 
    {
        $this->refreshPage = $refresh;
        return $this;
    }

    /**
     * Create a deletion confirmation alert
     * 
     * @param string $deleteUrl URL for delete action
     * @param string $itemName Name of item being deleted
     * @return self
     */
    public function confirmDeletion(string $deleteUrl, string $itemName = 'this item'): self 
    {
        $this->title = 'Are you sure?';
        $this->message = "Do you want to delete {$itemName}? This action cannot be undone.";
        $this->type = 'warning';
        $this->showCancelButton = true;
        $this->confirmButtonText = 'Delete';
        $this->cancelButtonText = 'Cancel';

        return $this;
    }

    /**
     * Generate JavaScript for SweetAlert2
     * 
     * @return string JavaScript code
     */
    public function generate(): string 
    {
        $alertOptions = [
            'title' => $this->title,
            'text' => $this->message,
            'icon' => $this->type,
            'confirmButtonText' => $this->confirmButtonText,
            'showCancelButton' => $this->showCancelButton,
            'cancelButtonText' => $this->cancelButtonText,
        ];

        if ($this->timer !== null) {
            $alertOptions['timer'] = $this->timer;
            $alertOptions['timerProgressBar'] = true;
        }

        $jsonOptions = json_encode($alertOptions);

        $script = <<<HTML
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({$jsonOptions}).then((result) => {
                if (result.isConfirmed) {
        HTML;

        // Handle redirect or page refresh
        if ($this->redirectUrl) {
            $script .= "window.location.href = '{$this->redirectUrl}';";
        } elseif ($this->refreshPage) {
            $script .= "window.location.reload();";
        }

        $script .= <<<HTML
                }
            });
        });
        </script>
        HTML;

        return $script;
    }

    /**
     * Magic method to directly output the alert
     * 
     * @return string
     */
    public function __toString(): string 
    {
        return $this->generate();
    }
}
?>